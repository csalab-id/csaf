FROM debian:11
LABEL maintainer="admin@csalab.id"
WORKDIR /opt
RUN apt-get update && \
    apt-get -y upgrade && \
    apt-get -y install wget
RUN useradd -m -c "Infection Monkey" -s /bin/bash -d /home/monkey monkey && \
    wget --progress=dot:giga https://github.com/guardicore/monkey/releases/download/v2.3.0/InfectionMonkey-v2.3.0.AppImage -O /opt/InfectionMonkey-v2.3.0.AppImage && \
    chmod +x InfectionMonkey-v2.3.0.AppImage && \
    ./InfectionMonkey-v2.3.0.AppImage --appimage-extract && \
    rm -rf InfectionMonkey-v2.3.0.AppImage && \
    mv squashfs-root infectionmonkey && \
    chown monkey:monkey infectionmonkey -Rh && \
    sed -i "s/5000/443/g" /opt/infectionmonkey/usr/src/monkey_island/cc/server_utils/consts.py
RUN apt-get clean all && \
    rm -rf /var/lib/apt/lists/*
USER monkey
WORKDIR /opt/infectionmonkey
ENTRYPOINT [ "/opt/infectionmonkey/AppRun" ]