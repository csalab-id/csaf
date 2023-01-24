#!/bin/sh

cd /OctopusWAF/
/OctopusWAF/bin/OctopusWAF -h 0.0.0.0:80 -r ${BACKEND} -m horspool --debug --libinjection-sqli --pcre