FROM kalilinux/kali-rolling:amd64
RUN apt update && \
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
apt -y dist-upgrade && \
apt -y autoremove && \
apt clean all