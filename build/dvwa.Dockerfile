FROM debian:11
COPY ./data/dvwa/ /var/www/html/
RUN apt update && \
apt -y upgrade && \
DEBIAN_FRONTEND=noninteractive apt -yq install lsb-release curl openssh-server apache2 libapache2-mod-php dialog php php-gd php-mysql && \
curl -so wazuh-agent-4.3.10.deb https://packages.wazuh.com/4.x/apt/pool/main/w/wazuh-agent/wazuh-agent_4.3.10-1_amd64.deb && \
WAZUH_MANAGER='wazuh-manager.lab' WAZUH_AGENT_GROUP='default' dpkg -i ./wazuh-agent-4.3.10.deb && \
update-rc.d wazuh-agent defaults 95 10 && \
curl -so splunkforwarder-9.0.4-de405f4a7979-linux-2.6-amd64.deb "https://download.splunk.com/products/universalforwarder/releases/9.0.4/linux/splunkforwarder-9.0.4-de405f4a7979-linux-2.6-amd64.deb" && \
dpkg -i splunkforwarder-9.0.4-de405f4a7979-linux-2.6-amd64.deb && \
/opt/splunkforwarder/bin/splunk add user admin -role Admin -password splunkpassword --no-prompt --accept-license --answer-yes && \
cd /var/www/html/ && \
tar -xf git.tar.gz && \
rm -rf /wazuh-agent-4.3.10.deb /splunkforwarder-9.0.4-de405f4a7979-linux-2.6-amd64.deb /var/www/html/index.html /var/www/html/git.tar.gz
 