FROM kalilinux/kali-rolling:amd64
LABEL maintainer="admin@csalab.id"
WORKDIR /root                                       
RUN sed -i "s/http.kali.org/mirrors.ocf.berkeley.edu/g" /etc/apt/sources.list && \
apt update && \
apt -y upgrade
RUN DEBIAN_FRONTEND=noninteractive apt -yq install \
  dialog \
  firefox-esr \
  inetutils-ping \
  htop \
  nano \
  net-tools \
  tigervnc-standalone-server \
  tigervnc-xorg-extension \
  tigervnc-viewer \
  novnc
RUN DEBIAN_FRONTEND=noninteractive apt -yq install \
  dbus-x11 \
  xfce4-session \
  xfce4-goodies \
  kali-linux-large \
  kali-desktop-xfce
RUN apt -y full-upgrade && \
apt -y autoremove && \
apt clean all
COPY script/kalilinux.startup.sh /src/startup.sh
COPY script/kalilinux.tunell.py /src/tunell.py
ENV PASSWORD=attack
ENV SHELL=/bin/bash
EXPOSE 8080
ENTRYPOINT ["/bin/bash", "/src/startup.sh"]