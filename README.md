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

# Exposed Ports
An exposed port can be accessed using a proxy http client, SSH client, or HTTP client. Choose one for the best experience.
- Port 6080 (Access to attack network)
- Port 7080 (Access to defense network)
- Port 8080 (Access to monitor network)

# Domain Access
- http://attack.lab:6080/vnc.html (password: attack)
- http://defense.lab:7080/vnc.html (password: defense)
- http://monitor.lab:8080/vnc.html (password: monitor)
- http://phising.lab/
- http://10.0.0.200:8081/
- https://gophish.lab:3333/
- http://server.lab/ (username: postmaster@server.lab, passowrd: supersecretpassword)
- http://server.lab/iredadmin/ (username: postmaster@server.lab, passowrd: supersecretpassword)
- http://mail.server.lab/ (username: postmaster@server.lab, passowrd: supersecretpassword)
- http://mail.server.lab/iredadmin/ (username: postmaster@server.lab, passowrd: supersecretpassword)
- http://gitea.lab/ (username: csalab, password: giteapassword)
- http://dvwa.lab/ (username: admin, passowrd: password)
- http://dvwa-monitor.lab/ (username: admin, passowrd: password)
- http://dvwa-modsecurity.lab/ (username: admin, passowrd: password)
- http://dvwa-octopuswaf.lab/ (username: admin, passowrd: password)
- https://wazuh-indexer.lab:9200/ (username: admin, passowrd: SecretPassword)
- https://wazuh-manager.lab/
- https://wazuh-dashboard.lab:5601/ (username: admin, passowrd: SecretPassword)
- http://splunk.lab:8000/ (username: admin, password: splunkpassword)

# Network / IP Address

## Attack
- 10.0.0.100 (attack.lab)
- 10.0.0.200 (phising.lab)
- 10.0.0.201 (server.lab / mail.server.lab)
- 10.0.0.202 (gophish.lab)

## Defense
- 10.0.1.100 (attack.lab)
- 10.0.1.101 (defense.lab)
- 10.0.1.10 (dvwa.lab)
- 10.0.1.12 (dvwa-modsecurity.lab)
- 10.0.1.13 (dvwa-octopuswaf.lab)
- 10.0.1.20 (gitea.lab)

## Monitor
- 10.1.0.10 (dvwa.lab)
- 10.1.0.11 (dvwa-monitor.lab)
- 10.1.0.12 (dvwa-modsecurity.lab)
- 10.1.0.13 (dvwa-octopuswaf.lab)
- 10.1.0.102 (monitor.lab)
- 10.1.0.30 (wazuh-manager.lab)
- 10.1.0.31 (wazuh-indexer.lab)
- 10.1.0.32 (wazuh-dashboard.lab)
- 10.1.0.40 (splunk.lab)

## Public
- 10.0.2.101 (defense.lab)
- 10.0.2.102 (monitor.lab)

# License
This Docker Compose application is released under the MIT License. See the [LICENSE](https://www.mit.edu/~amini/LICENSE.md) file for details.
