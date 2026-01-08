FROM debian:11
LABEL maintainer="admin@csalab.id"
COPY ./data/dvwa/ /var/www/html/
WORKDIR /var/www/html/
RUN apt-get update && \
    apt-get -y upgrade && \
    DEBIAN_FRONTEND=noninteractive apt-get -yq install \
        inetutils-ping \
        git \
        lsb-release \
        curl \
        wget \
        jq \
        openssh-server \
        rsyslog \
        apache2 \
        libapache2-mod-php \
        dialog \
        php \
        php-gd \
        php-mysql && \
    (echo "sshpassword"; echo "sshpassword") | passwd
RUN wget -q "https://packages.wazuh.com/4.x/apt/pool/main/w/wazuh-agent/wazuh-agent_4.13.0-1_amd64.deb" && \
    WAZUH_MANAGER='wazuh-manager.lab' WAZUH_AGENT_GROUP='default' dpkg -i ./wazuh-agent_4.13.0-1_amd64.deb && \
    update-rc.d wazuh-agent defaults 95 10 && \
    wget -q "https://download.splunk.com/products/universalforwarder/releases/10.0.2/linux/splunkforwarder-10.0.2-e2d18b4767e9-linux-amd64.deb" && \
    dpkg -i splunkforwarder-10.0.2-e2d18b4767e9-linux-amd64.deb && \
    /opt/splunkforwarder/bin/splunk add user admin -role Admin -password splunkpassword --no-prompt --accept-license --answer-yes && \
    sed -i "s/PYTHONHTTPSVERIFY=0/PYTHONHTTPSVERIFY=1/g" /opt/splunkforwarder/etc/splunk-launch.conf && \
    sed -i "s/SPLUNK_OS_USER=rootfwd/SPLUNK_OS_USER=root/g" /opt/splunkforwarder/etc/splunk-launch.conf && \
    sed -i "s/SPLUNK_OS_USER=splunkfwd/SPLUNK_OS_USER=root/g" /opt/splunkforwarder/etc/splunk-launch.conf
COPY --chown=root:root --chmod=755 config/dvwa/velociraptor-client /etc/init.d/velociraptor-client
COPY --chown=root:root --chmod=644 config/velociraptor/client.config.yaml /etc/velociraptor-client.config.yaml
RUN wget -q "https://github.com/Velocidex/velociraptor/releases/download/v0.75/velociraptor-v0.75.6-linux-amd64" -O /usr/local/bin/velociraptor && \
    chmod +x /usr/local/bin/velociraptor && \
    update-rc.d velociraptor-client defaults
RUN sed -i "s/allow_url_include = Off/allow_url_include = On/g" /etc/php/*/apache2/php.ini && \
    sed -i "s/#PermitRootLogin prohibit-password/PermitRootLogin yes/g" /etc/ssh/sshd_config && \
    tar -xf git.tar.gz && \
    chmod 777 config/ hackable/uploads/ && \
    apt-get -y autoremove && \
    apt-get clean all && \
    rm -rf /var/lib/apt/lists/* \
        wazuh-agent_4.13.0-1_amd64.deb \
        splunkforwarder-10.0.2-e2d18b4767e9-linux-amd64.deb \
        /var/www/html/index.html \
        /var/www/html/git.tar.gz
COPY script/dvwa.startup.sh /startup.sh
COPY --chown=root:wazuh --chmod=660 config/dvwa/ossec.conf /var/ossec/etc/ossec.conf
COPY --chown=root:wazuh --chmod=750 config/dvwa/remove-threat.sh /var/ossec/active-response/bin/remove-threat.sh
ENTRYPOINT [ "/bin/bash", "/startup.sh" ]