# Cyber Security Awareness Framework (Docker)
A brief description of the project.

# Requirements
- Docker
- Docker-compose

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
docker-compose pull
```
Generate wazuh ssl certificate
```
docker-compose -f generate-indexer-certs.yml run --rm generator
```
For security reason you should set env like this first
```
export ATTACK_PASS=ChangeMePlease
export DEFENSE_PASS=ChangeMePlease
export MONITOR_PASS=ChangeMePlease
export SPLUNK_PASS=ChangeMePlease
export GOPHISH_PASS=ChangeMePlease
export MAIL_PASS=ChangeMePlease
```
Start the container
```
docker-compose up -d
```

# Exposed Ports
An exposed port can be accessed using a proxy socks5 client, SSH client, or HTTP client. Choose one for the best experience.
- Port 6080 (Access to attack network)
- Port 7080 (Access to defense network)
- Port 8080 (Access to monitor network)

# Example to access internal network with proxy socks5
- curl --proxy socks5://ipaddress:6080 http://10.0.0.100/vnc.html
- curl --proxy socks5://ipaddress:7080 http://10.0.1.101/vnc.html
- curl --proxy socks5://ipaddress:8080 http://10.0.3.102/vnc.html

# Example to remote ssh with ssh client
- ssh root@ipaddress -p 6080 (default password: attackpassword)
- ssh root@ipaddress -p 7080 (default password: defensepassword)
- ssh root@ipaddress -p 8080 (default password: monitorpassword)

# Example to access kali linux desktop with curl / browser
- curl http://ipaddress:6080/vnc.html
- curl http://ipaddress:7080/vnc.html
- curl http://ipaddress:8080/vnc.html

# Domain Access
- http://attack.lab:6080/vnc.html (default password: attackpassword)
- http://defense.lab:7080/vnc.html (default password: defensepassword)
- http://monitor.lab:8080/vnc.html (default password: monitorpassword)
- https://gophish.lab:3333/ (default username: admin, default password: gophishpassword)
- https://server.lab/ (default username: postmaster@server.lab, default passowrd: mailpassword)
- https://server.lab/iredadmin/ (default username: postmaster@server.lab, default passowrd: mailpassword)
- https://mail.server.lab/ (default username: postmaster@server.lab, default passowrd: mailpassword)
- https://mail.server.lab/iredadmin/ (default username: postmaster@server.lab, default passowrd: mailpassword)
- http://phising.lab/
- http://10.0.0.200:8081/
- http://gitea.lab/ (default username: csalab, default password: giteapassword)
- http://dvwa.lab/ (default username: admin, default passowrd: password)
- http://dvwa-monitor.lab/ (default username: admin, default passowrd: password)
- http://dvwa-modsecurity.lab/ (default username: admin, default passowrd: password)
- http://wackopicko.lab/
- http://juiceshop.lab:3000/
- https://wazuh-indexer.lab:9200/ (default username: admin, default passowrd: SecretPassword)
- https://wazuh-manager.lab/
- https://wazuh-dashboard.lab:5601/ (default username: admin, default passowrd: SecretPassword)
- http://splunk.lab:8000/ (default username: admin, default password: splunkpassword)
- https://infectionmonkey.lab:5000/
- http://purpleops.lab/ (default username: admin@purpleops.lab, default password: purpleopspassword)
- http://caldera.lab:8888/ (default username: red/blue, default password: calderapassword)

# Network / IP Address

## Attack
- 10.0.0.100 attack.lab
- 10.0.0.200 phising.lab
- 10.0.0.201 server.lab
- 10.0.0.201 mail.server.lab
- 10.0.0.202 gophish.lab
- 10.0.0.110 infectionmonkey.lab
- 10.0.0.111 mongodb.lab
- 10.0.0.112 purpleops.lab
- 10.0.0.113 caldera.lab

## Defense
- 10.0.1.101 defense.lab
- 10.0.1.10 dvwa.lab
- 10.0.1.13 wackopicko.lab
- 10.0.1.20 gitea.lab

## Public
- 10.0.2.101 defense.lab
- 10.0.2.13 wackopicko.lab

## Monitor
- 10.0.3.102 monitor.lab
- 10.0.3.9 mariadb.lab
- 10.0.3.10 dvwa.lab
- 10.0.3.11 dvwa-monitor.lab
- 10.0.3.12 dvwa-modsecurity.lab
- 10.0.3.30 wazuh-manager.lab
- 10.0.3.31 wazuh-indexer.lab
- 10.0.3.32 wazuh-dashboard.lab
- 10.0.3.40 splunk.lab

## Internet
- 10.0.4.102 monitor.lab
- 10.0.4.32 wazuh-dashboard.lab
- 10.0.4.40 splunk.lab

## Internal
- 10.0.5.100 attack.lab
- 10.0.5.12 dvwa-modsecurity.lab
- 10.0.5.13 wackopicko.lab
- 10.0.5.14 juiceshop.lab

# License
This Docker Compose application is released under the MIT License. See the [LICENSE](https://www.mit.edu/~amini/LICENSE.md) file for details.
