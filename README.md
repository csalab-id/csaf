![CSAF](https://raw.githubusercontent.com/csalab-id/csaf/main/.github/images/csaf.png)

[![Platform](https://img.shields.io/badge/platform-osx%2Flinux%2Fwindows-green.svg)](https://github.com/csalab-id/csaf/)
[![Join the chat](https://badges.gitter.im/repo.svg)](https://app.gitter.im/#/room/#csaf:gitter.im)
[![Docker Pulls](https://img.shields.io/docker/pulls/csalab/csaf?style=social)](https://hub.docker.com/r/csalab/csaf/)
[![Documentation](https://readthedocs.org/projects/csaf/badge/?version=latest)](https://csaf.readthedocs.io/en/latest/)

The Cyber Security Awareness Framework (CSAF) is a structured approach aimed at enhancing cybersecurity awareness and understanding among individuals, organizations, and communities. It provides guidance for the development of effective cybersecurity awareness programs, covering key areas such as assessing awareness needs, creating educational materials, conducting training and simulations, implementing communication campaigns, and measuring awareness levels. By adopting this framework, organizations can foster a robust security culture, enhance their ability to detect and respond to cyber threats, and mitigate the risks associated with attacks and security breaches.

# Requirements

## Software
- Docker
- Docker Compose plugin

## Hardware

### Minimum
- 8 Core CPU
- 16GB RAM
- 128GB Disk free

### Recommendation
- 12 Core CPU or above
- 32GB RAM or above
- 256GB Disk free or above

# Installation

Clone the repository
```
git clone https://github.com/csalab-id/csaf.git
```
Navigate to the project directory
```
cd csaf
```
Pull the Docker images
```
docker compose --profile=all pull
```
Generate Wazuh SSL certificate
```
docker compose -f generate-certs.yml run --rm generator
```

# Prepare .env File
Create a local environment file for Docker Compose:
```
cp .env.example .env
```
Update values in `.env` as needed, or use shell exports below.

# Environment Variables
Set these before running Docker Compose (defaults come from [docker-compose.yml](docker-compose.yml)):
- ATTACK_PASS / DEFENSE_PASS / MONITOR_PASS: VNC passwords for attack, defense, and monitor hosts (defaults: attackpassword, defensepassword, monitorpassword)
- SPLUNK_PASS: Splunk admin password (default: splunkpassword)
- VELOX_PASS: Velociraptor admin password (default: veloxpassword)
- GOPHISH_PASS: Initial Gophish admin password (default: gophishpassword)
- MAIL_PASS: First mail domain admin password for iRedMail (default: mailpassword)
- PHISHING_URL: Target URL to clone for the phishing page (default: https://gmail.com/)
- PHISHING_TITLE: Page title for the phishing site (default: Gmail)
- PHISHING_FAVICON: Favicon URL for the phishing site (default: https://www.google.com/favicon.ico)
- BIND_ADDR: Bind address for exposed attack/defense/monitor services (default: 0.0.0.0)

Example:
```
export ATTACK_PASS=ChangeMePlease
export DEFENSE_PASS=ChangeMePlease
export MONITOR_PASS=ChangeMePlease
export SPLUNK_PASS=ChangeMePlease
export VELOX_PASS=ChangeMePlease
export GOPHISH_PASS=ChangeMePlease
export MAIL_PASS=ChangeMePlease
export PHISHING_URL=https://example.com/
export PHISHING_TITLE="Example Login"
export PHISHING_FAVICON=https://example.com/favicon.ico
export BIND_ADDR=127.0.0.1
```

Start all the containers
```
docker compose --profile=all up -d
```

You can run specific labs with these profiles
- all
- attackdefenselab
- phishinglab
- breachlab
- soclab
- ransomwarelab

For example
```
docker compose --profile=attackdefenselab up -d
```

# Profiles
- all: Starts every service in the stack.
- attackdefenselab: Attack/Defense desktops, DVWA (+ secure + ModSecurity), WackoPicko, Juice Shop, Gitea, MariaDB.
- attackdefenselab: Attack/Defense desktops, DVWA (+ secure + ModSecurity), WackoPicko, Juice Shop, Gitea, MariaDB, Bunkerweb (reverse proxy/WAF).
- phishinglab: Attack desktop, Gophish, Phishing site, iRedMail server.
- breachlab: Attack/Defense desktops, DVWA, WackoPicko, Infection Monkey, MongoDB, Caldera.
- soclab: Monitor desktop, DVWA (+ secure + ModSecurity), Juice Shop, MariaDB, Wazuh (manager/indexer/dashboard), Splunk, Velociraptor, Bunkerweb (reverse proxy/WAF).
- ransomwarelab: Attack/Defense/Monitor desktops, Ransomware service.

# Services Overview
- Desktops: `attack.lab` (VNC on 6080), `defense.lab` (7080), `monitor.lab` (8080).
- Web apps: `dvwa.lab`, `wackopicko.lab`, `juiceshop.lab`, `gitea.lab`.
- Mail/Phishing: `mail.server.lab` (iRedMail), `gophish.lab`, `phishing.lab`.
- Breach simulation: `infectionmonkey.lab`, `mongodb.lab`, `caldera.lab`.
- Ransomware: `ransomware.lab`.
- SOC tooling: `wazuh-manager.lab`, `wazuh-indexer.lab`, `wazuh-dashboard.lab`, `splunk.lab`, `velociraptor.lab`.
- Security/Proxy: `bunkerweb.lab` reverse proxy/WAF for DVWA, Juice Shop, and WackoPicko. Setup UI: `https://bunkerweb.lab/setup`. Aliases: `dvwa-bunkerweb.lab`, `juiceshop-bunkerweb.lab`, `wackopicko-bunkerweb.lab`.

# Default Credentials
- VNC: `attackpassword` / `defensepassword` / `monitorpassword` (override via env).
- DVWA, WackoPicko, Juice Shop: default app passwords per app (see Domain Access list).
- Gitea: `csalab` / `giteapassword`.
- Gophish: admin password from `GOPHISH_PASS`.
- iRedMail: `postmaster@server.lab` / `mailpassword`.
- Splunk: `admin` / `splunkpassword`.
- Velociraptor: `admin` / `veloxpassword`.
- Wazuh Dashboard/Indexer: `admin` / `SecretPassword`.

# Networks
- attack: 10.0.0.0/24 (external lab attack network).
- defense: 10.0.1.0/24 (internal true) defense network.
- public: 10.0.2.0/24 (public-exposed subset).
- monitor: 10.0.3.0/24 (internal true) monitoring network.
- internet: 10.0.4.0/24 (simulated internet facing network).
- internal: 10.0.5.0/24 (service-to-service internal).

# Persistent Data
- Volumes keep state for databases and apps (MariaDB, Wazuh, Splunk, Gitea, etc.).
- Reset lab state: `docker compose down -v` removes containers and volumes.

# Lifecycle Commands
- Start (all): `docker compose --profile=all up -d`.
- Start (specific): `docker compose --profile=<profile> up -d`.
- Stop: `docker compose down`.
- Status: `docker compose ps`.
- Logs: `docker compose logs -f <service>`.

# Troubleshooting
- Certificates: Run `docker compose -f generate-certs.yml run --rm generator` before first SOC lab start.
- Bind address: Set `BIND_ADDR=127.0.0.1` to bind services locally.
- Port conflicts: Change host ports or stop conflicting processes.
- Clean slate: Use `docker compose down -v` to wipe persistent data.

# Security Notes
- Change all default passwords via `.env` before exposing services.
- Avoid exposing services broadly; prefer `BIND_ADDR=127.0.0.1` and access via SOCKS5 or SSH.
- Be cautious with email and phishing services; use test domains and isolated networks only.

# Proof
![Caldera](https://raw.githubusercontent.com/csalab-id/csaf/main/.github/images/caldera.png)
![Secure DVWA](https://raw.githubusercontent.com/csalab-id/csaf/main/.github/images/dvwa_modsecurity.png)
![Gitea](https://raw.githubusercontent.com/csalab-id/csaf/main/.github/images/gitea.png)
![Gophish](https://raw.githubusercontent.com/csalab-id/csaf/main/.github/images/gophish.png)
![Infectionmonkey](https://raw.githubusercontent.com/csalab-id/csaf/main/.github/images/infectionmonkey.png)
![Iredmail](https://raw.githubusercontent.com/csalab-id/csaf/main/.github/images/iredmail.png)
![Juice Shop](https://raw.githubusercontent.com/csalab-id/csaf/main/.github/images/juiceshop.png)
![Mitmproxy](https://raw.githubusercontent.com/csalab-id/csaf/main/.github/images/mitmproxy.png)
![Phishing](https://raw.githubusercontent.com/csalab-id/csaf/main/.github/images/phishing.png)
![Roundcube](https://raw.githubusercontent.com/csalab-id/csaf/main/.github/images/roundcube.png)
![Splunk](https://raw.githubusercontent.com/csalab-id/csaf/main/.github/images/splunk.png)
![Wackopicko](https://raw.githubusercontent.com/csalab-id/csaf/main/.github/images/wackopicko.png)
![Wazuh](https://raw.githubusercontent.com/csalab-id/csaf/main/.github/images/wazuh.png)

# Exposed Ports
An exposed port can be accessed using a SOCKS5 proxy, SSH client, or HTTP client. Choose one for the best experience.

- Port 6080 (Access to attack network)
- Port 7080 (Access to defense network)
- Port 8080 (Access to monitor network)

# Example Usage
## Access Internal Network with SOCKS5 Proxy
- curl --proxy socks5://ipaddress:6080 http://10.0.0.100/vnc.html
- curl --proxy socks5://ipaddress:7080 http://10.0.1.101/vnc.html
- curl --proxy socks5://ipaddress:8080 http://10.0.3.102/vnc.html

## Remote SSH with SSH Client
- ssh kali@ipaddress -p 6080 (default password: attackpassword)
- ssh kali@ipaddress -p 7080 (default password: defensepassword)
- ssh kali@ipaddress -p 8080 (default password: monitorpassword)

## Access Kali Linux Desktop (cURL/Browser)
- curl http://ipaddress:6080/vnc.html
- curl http://ipaddress:7080/vnc.html
- curl http://ipaddress:8080/vnc.html

# Domain Access
- http://attack.lab/vnc.html (default password: attackpassword)
- http://defense.lab/vnc.html (default password: defensepassword)
- http://monitor.lab/vnc.html (default password: monitorpassword)
- https://gophish.lab/ (default username: admin, default password: gophishpassword)
- https://server.lab/ (default username: postmaster@server.lab, default password: mailpassword)
- https://server.lab/iredadmin/ (default username: postmaster@server.lab, default password: mailpassword)
- https://mail.server.lab/ (default username: postmaster@server.lab, default password: mailpassword)
- https://mail.server.lab/iredadmin/ (default username: postmaster@server.lab, default password: mailpassword)
- http://phishing.lab/
- http://ransomware.lab/
- http://10.0.0.200:8081/
- http://gitea.lab/ (default username: csalab, default password: giteapassword)
- http://dvwa.lab/ (default username: admin, default password: password)
- http://dvwa-monitor.lab/ (default username: admin, default password: password)
- http://dvwa-modsecurity.lab/ (default username: admin, default password: password)
- https://bunkerweb.lab/setup
- http://dvwa-bunkerweb.lab/ (default username: admin, default password: password)
- http://wackopicko-bunkerweb.lab/
- http://juiceshop-bunkerweb.lab/
- http://wackopicko.lab/
- http://juiceshop.lab/
- https://wazuh-indexer.lab:9200/ (default username: admin, default password: SecretPassword)
- https://wazuh-manager.lab/
- https://wazuh-dashboard.lab/ (default username: admin, default password: SecretPassword)
- http://splunk.lab/ (default username: admin, default password: splunkpassword)
- https://velociraptor.lab/ (default username: admin, default password: veloxpassword)
- https://infectionmonkey.lab:5000/
- http://caldera.lab/ (default username: red/blue, default password: calderapassword)

# Network / IP Address

## Attack
- 10.0.0.100 attack.lab
- 10.0.0.200 phishing.lab
- 10.0.0.201 server.lab
- 10.0.0.201 mail.server.lab
- 10.0.0.202 gophish.lab
- 10.0.0.203 ransomware.lab
- 10.0.0.110 infectionmonkey.lab
- 10.0.0.111 mongodb.lab
- 10.0.0.113 caldera.lab

## Defense
- 10.0.1.101 defense.lab
- 10.0.1.10 dvwa.lab
- 10.0.1.13 wackopicko.lab
- 10.0.1.14 juiceshop.lab
- 10.0.1.20 gitea.lab
- 10.0.1.21 bunkerweb.lab
- 10.0.1.21 dvwa-bunkerweb.lab
- 10.0.1.21 wackopicko-bunkerweb.lab
- 10.0.1.21 juiceshop-bunkerweb.lab
- 10.0.1.110 infectionmonkey.lab
- 10.0.1.113 caldera.lab
- 10.0.1.203 ransomware.lab

## Monitor
- 10.0.3.201 server.lab
- 10.0.3.201 mail.server.lab
- 10.0.3.203 ransomware.lab
- 10.0.3.9 mariadb.lab
- 10.0.3.10 dvwa.lab
- 10.0.3.11 dvwa-monitor.lab
- 10.0.3.12 dvwa-modsecurity.lab
- 10.0.3.21 bunkerweb.lab
- 10.0.3.21 dvwa-bunkerweb.lab
- 10.0.3.21 wackopicko-bunkerweb.lab
- 10.0.3.21 juiceshop-bunkerweb.lab
- 10.0.3.102 monitor.lab
- 10.0.3.30 wazuh-manager.lab
- 10.0.3.31 wazuh-indexer.lab
- 10.0.3.32 wazuh-dashboard.lab
- 10.0.3.40 splunk.lab
- 10.0.3.41 velociraptor.lab

## Public
- 10.0.2.101 defense.lab
- 10.0.2.13 wackopicko.lab

## Internet
- 10.0.4.102 monitor.lab
- 10.0.4.30 wazuh-manager.lab
- 10.0.4.32 wazuh-dashboard.lab
- 10.0.4.40 splunk.lab
- 10.0.4.41 velociraptor.lab

## Internal
- 10.0.5.100 attack.lab
- 10.0.5.12 dvwa-modsecurity.lab
- 10.0.5.13 wackopicko.lab
- 10.0.5.21 bunkerweb.lab
- 10.0.5.21 dvwa-bunkerweb.lab
- 10.0.5.21 wackopicko-bunkerweb.lab
- 10.0.5.21 juiceshop-bunkerweb.lab

# License
This Docker Compose application is released under the MIT License. See the [LICENSE](https://www.mit.edu/~amini/LICENSE.md) file for details.

# Disclaimer
This project is intended for educational and lab use only. Do not expose any provided services directly to the internet or production environments without hardening and independent security validation. You are solely responsible for complying with applicable laws, regulations, and organizational policies when deploying or using this project.
