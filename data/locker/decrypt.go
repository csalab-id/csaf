package main

import (
	"fmt"
	"os"
	"sort"
)

func main() {
	home := getHomeDir()
	if home == "" {
		fmt.Fprintln(os.Stderr, "Cannot determine home directory")
		os.Exit(1)
	}

	var files []string
	scan(home, 0, 10, &files)
	// scan("/workspaces/csaf/data/locker/Desktop", 0, 10, &files)
	sort.Strings(files)

	password, err := generatePasswordFromMachineID()
	if err != nil {
		fmt.Fprintln(os.Stderr, "Cannot generate password from machine ID")
		os.Exit(1)
	}
	aesKey := deriveAESKey(password)
	defer clearString(&password)
	defer clearAESKey(&aesKey)

	successCount := 0
	failCount := 0

	for _, filePath := range files {
		if !isLockerFile(filePath) {
			continue
		}

		decrypted, err := decryptFile(filePath, aesKey)
		if err != nil {
			password, err := getMachineID()
			if err != nil {
				fmt.Fprintf(os.Stderr, "Error getting machine ID: %v\n", err)
				os.Exit(1)
			}
			aesKey := deriveAESKey(password)
			defer clearString(&password)
			defer clearAESKey(&aesKey)
			decrypted, err := decryptFile(filePath, aesKey)
			if err != nil {
				fmt.Fprintf(os.Stderr, "Error decrypting file: %v\n", err)
				continue
			}

			if err := writeDecryptedFile(filePath, decrypted); err != nil {
				fmt.Fprintf(os.Stderr, "Error writing file: %v\n", err)
				failCount++
				continue
			}

			originalPath := filePath[:len(filePath)-7]
			fmt.Printf("[OK] %s -> %s\n", filePath, originalPath)
			successCount++
		} else {
			if err := writeDecryptedFile(filePath, decrypted); err != nil {
				fmt.Fprintf(os.Stderr, "Error writing file: %v\n", err)
				failCount++
				continue
			}

			originalPath := filePath[:len(filePath)-7]
			fmt.Printf("[OK] %s -> %s\n", filePath, originalPath)
			successCount++
		}
	}
	fmt.Printf("\nDecryption completed: %d successful, %d failed\n", successCount, failCount)
}
