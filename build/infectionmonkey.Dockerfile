FROM debian:11
LABEL maintainer="admin@csalab.id"
RUN apt-get update && \
apt-get -y install wget && \
useradd -m -c "Infection Monkey" -s /bin/bash -d /home/monkey monkey && \
wget https://github.com/guardicore/monkey/releases/download/v2.3.0/InfectionMonkey-v2.3.0.AppImage -O /opt/InfectionMonkey-v2.3.0.AppImage && \
cd /opt/ && \
chmod +x InfectionMonkey-v2.3.0.AppImage && \
./InfectionMonkey-v2.3.0.AppImage --appimage-extract && \
rm -rf InfectionMonkey-v2.3.0.AppImage && \
mv squashfs-root infectionmonkey && \
chown monkey:monkey infectionmonkey -Rh
USER monkey
WORKDIR /opt/infectionmonkey
ENTRYPOINT [ "/opt/infectionmonkey/AppRun" ]