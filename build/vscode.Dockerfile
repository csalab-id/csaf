FROM ubuntu:22.04
RUN apt update && \
apt -y upgrade && \
apt -y install wget git && \
wget https://github.com/coder/code-server/releases/download/v4.9.1/code-server_4.9.1_amd64.deb && \
dpkg -i code-server_4.9.1_amd64.deb && \
rm -rf code-server_4.9.1_amd64.deb