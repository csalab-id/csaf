#!/bin/bash

echo '[+] Starting ssh...'
service ssh start

echo '[+] Starting apache'
service apache2 start

echo '[+] Starting wazuh-agent'
service wazuh-agent start

echo '[+] Starting splunk agent'
/opt/splunkforwarder/bin/splunk start

/opt/splunkforwarder/bin/splunk list forward-server -auth admin:splunkpassword | grep "splunk\.lab"
if [[ "$?" == "1" ]]; then
  /opt/splunkforwarder/bin/splunk add forward-server splunk.lab:9997 -auth admin:splunkpassword
fi

/opt/splunkforwarder/bin/splunk list monitor -auth admin:splunkpassword | grep "/var/log/apache2/"
if [[ "$?" == "1" ]]; then
  /opt/splunkforwarder/bin/splunk add monitor /var/log/apache2/ -auth admin:splunkpassword
fi

while true
do
    tail -f /var/log/apache2/*.log
    exit 0
done