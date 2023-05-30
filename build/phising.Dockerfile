FROM debian:bullseye-slim
RUN apt update && \
apt -y upgrade && \
DEBIAN_FRONTEND=noninteractive apt -yq install \
  openbox \
  firefox-esr \
  mitmproxy \
  curl \
  novnc \
  net-tools \
  tigervnc-standalone-server \
  tigervnc-xorg-extension \
  tigervnc-viewer && \
apt -y full-upgrade && \
apt -y autoremove && \
apt clean all