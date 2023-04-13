FROM ubuntu:22.04
RUN apt update && \
apt -y upgrade && \
DEBIAN_FRONTEND=noninteractive apt -yq install \
  curl \
  lsb-release \
  git \
  make \
  gcc \
  libpcre3-dev \
  libevent-dev \
  libssl-dev && \
git clone https://git.code.sf.net/p/octopuswaf/code OctopusWAF && \
cd OctopusWAF && \
make && \
curl -so wazuh-agent-4.3.10.deb https://packages.wazuh.com/4.x/apt/pool/main/w/wazuh-agent/wazuh-agent_4.3.10-1_amd64.deb && \
WAZUH_MANAGER='wazuh-manager.lab' WAZUH_AGENT_GROUP='default' dpkg -i ./wazuh-agent-4.3.10.deb && \
rm -rf wazuh-agent-4.3.10.deb && \
update-rc.d wazuh-agent defaults 95 10
