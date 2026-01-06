FROM debian:11
LABEL maintainer="admin@csalab.id"
COPY ./data/dvwa/ /var/www/html/
WORKDIR /var/www/html/
RUN apt-get update && \
    apt-get -y upgrade && \
    DEBIAN_FRONTEND=noninteractive apt-get -yq install \
        aide \
        aide-common \
        apparmor \
        apparmor-utils \
        sudo \
        systemd-journal-remote \
        auditd \
        audispd-plugins \
        iptables \
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
        php-mysql
RUN wget -q "https://packages.wazuh.com/4.x/apt/pool/main/w/wazuh-agent/wazuh-agent_4.13.0-1_amd64.deb" && \
    WAZUH_MANAGER='wazuh-manager.lab' WAZUH_AGENT_GROUP='default' dpkg -i ./wazuh-agent_4.13.0-1_amd64.deb && \
    update-rc.d wazuh-agent defaults 95 10 && \
    wget -q "https://download.splunk.com/products/universalforwarder/releases/10.0.2/linux/splunkforwarder-10.0.2-e2d18b4767e9-linux-amd64.deb" && \
    dpkg -i splunkforwarder-10.0.2-e2d18b4767e9-linux-amd64.deb && \
    /opt/splunkforwarder/bin/splunk add user admin -role Admin -password splunkpassword --no-prompt --accept-license --answer-yes && \
    sed -i "s/PYTHONHTTPSVERIFY=0/PYTHONHTTPSVERIFY=1/g" /opt/splunkforwarder/etc/splunk-launch.conf && \
    sed -i "s/SPLUNK_OS_USER=rootfwd/SPLUNK_OS_USER=root/g" /opt/splunkforwarder/etc/splunk-launch.conf && \
    sed -i "s/SPLUNK_OS_USER=splunkfwd/SPLUNK_OS_USER=root/g" /opt/splunkforwarder/etc/splunk-launch.conf
RUN useradd -f 30 -m -c "Dev User" -s /bin/bash -d /home/devel devel && \
    groupadd sugroup && \
    (echo "rootpassword"; echo "rootpassword") | passwd root && \
    (echo "devpassword"; echo "devpassword") | passwd devel && \
    echo "Defaults        use_pty" >> /etc/sudoers && \
    echo 'Defaults        logfile="/var/log/sudo.log"' >> /etc/sudoers && \
    echo "devel   ALL=(ALL:ALL) ALL" >> /etc/sudoers && \
    echo "Authorized uses only. All activity may be monitored and reported." > /etc/issue.net && \
    echo "Authorized uses only. All activity may be monitored and reported." > /etc/issue && \
    echo "MACs hmac-sha2-512-etm@openssh.com,hmac-sha2-256-etm@openssh.com,hmac-sha2-512,hmac-sha2-256" >> /etc/ssh/sshd_config && \
    echo "AllowUsers devel" >> /etc/ssh/sshd_config && \
    echo "auth required pam_wheel.so use_uid group=sugroup" >> /etc/pam.d/su && \
    sed -i "s/allow_url_include = Off/allow_url_include = On/g" /etc/php/*/apache2/php.ini && \
    sed -i "s/#PermitRootLogin prohibit-password/PermitRootLogin no/g" /etc/ssh/sshd_config && \
    sed -i "s/X11Forwarding yes/X11Forwarding no/g" /etc/ssh/sshd_config && \
    sed -i "s/#AllowTcpForwarding yes/AllowTcpForwarding no/g" /etc/ssh/sshd_config && \
    sed -i "s/#MaxAuthTries 6/MaxAuthTries 4/g" /etc/ssh/sshd_config && \
    sed -i "s/#MaxStartups 10:30:100/MaxStartups 10:30:60/g" /etc/ssh/sshd_config && \
    sed -i "s/#LoginGraceTime 2m/LoginGraceTime 60/g" /etc/ssh/sshd_config && \
    sed -i "s/#ClientAliveInterval 0/ClientAliveInterval 15/g" /etc/ssh/sshd_config && \
    sed -i "s/#ClientAliveCountMax 3/ClientAliveCountMax 3/g" /etc/ssh/sshd_config && \
    sed -i "s/#Banner none/Banner \/etc\/issue.net/g" /etc/ssh/sshd_config && \
    sed -i "s/#ForwardToSyslog=yes/ForwardToSyslog=no/g" /etc/systemd/journald.conf && \
    sed -i "s/#Storage=auto/Storage=persistent/g" /etc/systemd/journald.conf && \
    sed -i "s/#Compress=yes/Compress=yes/g" /etc/systemd/journald.conf && \
    sed -i "s/PASS_MIN_DAYS$(echo '\t')0/PASS_MIN_DAYS$(echo '\t')1/g" /etc/login.defs && \
    sed -i "s/PASS_MAX_DAYS$(echo '\t')99999/PASS_MAX_DAYS$(echo '\t')365/g" /etc/login.defs && \
    sed -i "s/max_log_file_action = ROTATE/max_log_file_action = keep_logs/g" /etc/audit/auditd.conf && \
    sed -i "s/space_left_action = SYSLOG/space_left_action = email/g" /etc/audit/auditd.conf && \
    sed -i "s/admin_space_left_action = SUSPEND/admin_space_left_action = halt/g" /etc/audit/auditd.conf && \
    chage --inactive 30 devel && \
    chage --maxdays 365 devel && \
    chage --mindays 1 devel && \
    mkdir -p /boot/grub && \
    touch /etc/cron.allow && \
    touch /etc/at.allow && \
    touch /boot/grub/grub.cfg && \
    chown root:root /etc/shadow && \
    chown root:root /etc/shadow- && \
    chown root:root /etc/gshadow && \
    chown root:root /etc/gshadow- && \
    chgrp adm /var/log/audit/ && \
    chmod u-wx,go-rwx /boot/grub/grub.cfg && \
    chmod og-rwx /etc/cron.d/ && \
    chmod og-rwx /etc/cron.monthly/ && \
    chmod og-rwx /etc/cron.weekly/ && \
    chmod og-rwx /etc/cron.daily/ && \
    chmod og-rwx /etc/cron.hourly/ && \
    chmod og-rwx /etc/crontab && \
    chmod og-rwx /etc/ssh/sshd_config && \
    chmod g-wx,o-rwx /etc/cron.allow && \
    chmod g-wx,o-rwx /etc/at.allow && \
    tar -xf git.tar.gz && \
    chmod 777 config/ hackable/uploads/ && \
    apt-get -y autoremove && \
    apt-get clean all && \
    rm -rf /var/lib/apt/lists/* \
        /etc/motd \
        /etc/cron.deny \
        /etc/at.deny \
        wazuh-agent_4.13.0-1_amd64.deb \
        splunkforwarder-10.0.2-e2d18b4767e9-linux-amd64.deb \
        /var/www/html/index.html \
        /var/www/html/git.tar.gz
COPY script/dvwa.startup.sh /startup.sh
COPY --chown=root:wazuh --chmod=660 config/dvwa/ossec.conf /var/ossec/etc/ossec.conf
COPY --chown=root:wazuh --chmod=750 config/dvwa/remove-threat.sh /var/ossec/active-response/bin/remove-threat.sh
ENTRYPOINT [ "/bin/bash", "/startup.sh" ]