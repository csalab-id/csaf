FROM kalilinux/kali-rolling:amd64
LABEL maintainer="admin@csalab.id"
WORKDIR /root                                       
RUN sed -i "s/http.kali.org/mirrors.ocf.berkeley.edu/g" /etc/apt/sources.list && \
apt-get update && \
apt-get -y upgrade && \
apt-get clean all && \
rm -rf /var/lib/apt/lists/*
RUN DEBIAN_FRONTEND=noninteractive apt-get -yq install \
  dialog \
  firefox-esr \
  inetutils-ping \
  htop \
  nano \
  net-tools \
  tigervnc-standalone-server \
  tigervnc-xorg-extension \
  tigervnc-viewer \
  novnc \
  dbus-x11 \
  xfce4-session \
  xfce4-goodies \
  kali-linux-large \
  kali-desktop-xfce
RUN apt-get -y full-upgrade && \
apt-get -y autoremove && \
apt-get clean all && \
rm -rf /var/lib/apt/lists/*
COPY script/kalilinux.startup.sh /src/startup.sh
COPY script/kalilinux.tunell.py /src/tunell.py
ENV PASSWORD=attack
ENV SHELL=/bin/bash
EXPOSE 8080
ENTRYPOINT ["/bin/bash", "/src/startup.sh"]