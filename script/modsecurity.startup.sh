#!/bin/bash

/etc/init.d/wazuh-agent start
httpd -DFOREGROUND
