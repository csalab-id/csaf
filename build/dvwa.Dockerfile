FROM debian:11
LABEL maintainer="admin@csalab.id"
COPY ./data/dvwa/ /var/www/html/
WORKDIR /var/www/html/
RUN apt-get update && \
apt-get -y upgrade && \
DEBIAN_FRONTEND=noninteractive apt-get -yq install git lsb-release curl openssh-server apache2 libapache2-mod-php dialog php php-gd php-mysql && \
(echo "sshpassword"; echo "sshpassword") | passwd && \
curl -so wazuh-agent-4.3.11.deb https://packages.wazuh.com/4.x/apt/pool/main/w/wazuh-agent/wazuh-agent_4.3.11-1_amd64.deb && \
WAZUH_MANAGER='wazuh-manager.lab' WAZUH_AGENT_GROUP='default' dpkg -i wazuh-agent-4.3.11.deb && \
update-rc.d wazuh-agent defaults 95 10 && \
curl -so splunkforwarder-9.1.0.1-77f73c9edb85-linux-2.6-amd64.deb "https://download.splunk.com/products/universalforwarder/releases/9.1.0.1/linux/splunkforwarder-9.1.0.1-77f73c9edb85-linux-2.6-amd64.deb" && \
dpkg -i splunkforwarder-9.1.0.1-77f73c9edb85-linux-2.6-amd64.deb && \
/opt/splunkforwarder/bin/splunk add user admin -role Admin -password splunkpassword --no-prompt --accept-license --answer-yes && \
sed -i "s/allow_url_include = Off/allow_url_include = On/g" /etc/php/*/apache2/php.ini && \
sed -i "s/PYTHONHTTPSVERIFY=0/PYTHONHTTPSVERIFY=1/g" /opt/splunkforwarder/etc/splunk-launch.conf && \
sed -i "s/SPLUNK_OS_USER=rootfwd/SPLUNK_OS_USER=root/g" /opt/splunkforwarder/etc/splunk-launch.conf && \
sed -i "s/SPLUNK_OS_USER=splunkfwd/SPLUNK_OS_USER=root/g" /opt/splunkforwarder/etc/splunk-launch.conf && \
sed -i "s/#PermitRootLogin prohibit-password/PermitRootLogin yes/g" /etc/ssh/sshd_config && \
tar -xf git.tar.gz && \
chmod 777 config/ hackable/uploads/ && \
rm -rf wazuh-agent-4.3.11.deb splunkforwarder-9.1.0.1-77f73c9edb85-linux-2.6-amd64.deb /var/www/html/index.html /var/www/html/git.tar.gz
COPY script/dvwa.startup.sh /startup.sh
ENTRYPOINT [ "/bin/bash", "/startup.sh" ]