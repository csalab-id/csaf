#!/bin/bash

echo '[+] Starting ssh...'
service ssh start

echo '[+] Starting apache'
service apache2 start

echo '[+] Starting wazuh-agent'
service wazuh-agent start

echo '[+] Starting splunk agent'
/opt/splunkforwarder/bin/splunk start

while true
do
    tail -f /var/log/apache2/*.log
    exit 0
done