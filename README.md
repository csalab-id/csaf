# Cyber Security Awareness Lab (Docker)
A brief description of the project.

# Requirements
- Docker
- Docker-compose

# Installation
Clone the repository
```
git clone https://github.com/csalab-id/csalab-docker.git
```
Navigate to the project directory
```
cd csalab-docker
```
Build the Docker image
```
docker-compose build
```
Generate wazuh ssl certificate
```
docker-compose -f generate-indexer-certs.yml run --rm generator
```
Start the container
```
docker-compose up -d
```

# Domain Access
- http://attack.lab:6080/vnc.html (password: attack)
- http://defense.lab:7080/vnc.html (password: defense)
- http://monitor.lab:8080/vnc.html (password: monitor)
- http://phising.lab/
- http://phising.lab:8081/
- http://server.lab/ (username: postmaster@server.lab, passowrd: supersecretpassword)
- http://mail.server.lab/
- http://dvwa.lab/
- http://dvwa-monitor.lab/
- http://dvwa-modsecurity.lab/
- http://dvwa-octopuswaf.lab/
- http://wazuh-indexer.lab:9200/
- https://wazuh-manager.lab/
- https://wazuh-dashboard.lab:5601/ (username: admin, passowrd: SecretPassword)

# Network / IP Address

## Attack
- 10.0.0.100 (attack.lab)
- 10.0.0.200 (phising.lab)
- 10.0.0.201 (server.lab / mail.server.lab)

## Defense
- 10.0.1.100 (attack.lab)
- 10.0.1.101 (defense.lab)
- 10.0.1.10 (dvwa.lab)
- 10.0.1.12 (dvwa-modsecurity.lab)
- 10.0.1.13 (dvwa-octopuswaf.lab)

## Monitor
- 10.1.0.10 (dvwa.lab)
- 10.1.0.11 (dvwa-monitor.lab)
- 10.1.0.12 (dvwa-modsecurity.lab)
- 10.1.0.13 (dvwa-octopuswaf.lab)
- 10.1.0.102 (monitor.lab)
- 10.1.0.30 (wazuh-manager.lab)
- 10.1.0.31 (wazuh-indexer.lab)
- 10.1.0.32 (wazuh-dashboard.lab)

## Public
- 10.0.2.101 (defense.lab)
- 10.0.2.102 (monitor.lab)

# License
This Docker Compose application is released under the MIT License. See the [LICENSE](https://www.mit.edu/~amini/LICENSE.md) file for details.