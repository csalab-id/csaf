FROM debian:bullseye-slim
RUN apt update && \
apt -y upgrade && \
DEBIAN_FRONTEND=noninteractive apt -yq install \
  openbox \
  firefox-esr \
  novnc \
  net-tools \
  tigervnc-standalone-server \
  tigervnc-xorg-extension \
  tigervnc-viewer && \
apt -y dist-upgrade && \
apt -y autoremove && \
apt clean all