FROM vulnerables/web-dvwa:latest
RUN apt update && \
DEBIAN_FRONTEND=noninteractive apt -yq install lsb-release curl && \
curl -so wazuh-agent-4.3.10.deb https://packages.wazuh.com/4.x/apt/pool/main/w/wazuh-agent/wazuh-agent_4.3.10-1_amd64.deb && \
WAZUH_MANAGER='wazuh-manager.lab' WAZUH_AGENT_GROUP='default' dpkg -i ./wazuh-agent-4.3.10.deb && \
rm -rf wazuh-agent-4.3.10.deb && \
update-rc.d wazuh-agent defaults 95 10
