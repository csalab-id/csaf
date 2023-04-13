#!/bin/bash

/etc/init.d/wazuh-agent start
/OctopusWAF/bin/OctopusWAF -h 0.0.0.0:80 -r ${BACKEND} -m horspool --debug --libinjection-sqli --pcre