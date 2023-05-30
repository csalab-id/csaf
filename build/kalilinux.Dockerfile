FROM kalilinux/kali-rolling:amd64
RUN sed -i "s/http.kali.org/mirrors.ocf.berkeley.edu/g" /etc/apt/sources.list && \
apt update && \
apt -y upgrade && \
DEBIAN_FRONTEND=noninteractive apt -yq install \
  dialog \
  firefox-esr \
  inetutils-ping \
  htop \
  nano \
  net-tools \
  tigervnc-standalone-server \
  tigervnc-xorg-extension \
  tigervnc-viewer \
  novnc && \
DEBIAN_FRONTEND=noninteractive apt -yq install \
  xfce4-session \
  xfce4-goodies \
  kali-linux-large \
  kali-desktop-xfce && \
apt -y full-upgrade && \
apt -y autoremove && \
apt clean all