#!/bin/bash

echo '[+] Starting wazuh-agent'
/etc/init.d/wazuh-agent start

#echo '[+] Starting splunk agent'
#/opt/splunkforwarder/bin/splunk start

#/opt/splunkforwarder/bin/splunk list forward-server -auth admin:splunkpassword | grep "splunk\.lab"
#if [[ "$?" == "1" ]]; then
#  /opt/splunkforwarder/bin/splunk add forward-server splunk.lab:9997 -auth admin:splunkpassword
#fi

#/opt/splunkforwarder/bin/splunk list monitor -auth admin:splunkpassword | grep "/var/log/apache2/"
#if [[ "$?" == "1" ]]; then
#  /opt/splunkforwarder/bin/splunk add monitor /var/log/apache2/ -auth admin:splunkpassword
#fi

echo '[+] Starting apache'
httpd -DFOREGROUND
