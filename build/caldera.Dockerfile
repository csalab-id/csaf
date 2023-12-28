FROM ghcr.io/mitre/caldera:master
LABEL maintainer="admin@csalab.id"
RUN apt-get update && \
    apt-get -yq install mingw-w64 upx && \
    source /opt/venv/caldera/bin/activate && \
    pip install docker myst && \
    sed -i "s/- access/- access\n- atomic/g" /usr/src/app/conf/local.yml && \
    sed -i "s/- debrief/- debrief\n- emu/g" /usr/src/app/conf/local.yml
WORKDIR /usr/src/app/plugins/emu
RUN sed -i "s/unzip payloads\/AdFind.zip/unzip -P NotMalware payloads\/AdFind.zip/g" download_payloads.sh && \
    sed -i "s/curl -o/curl -Lo/g" download_payloads.sh && \
    bash download_payloads.sh
RUN git clone https://github.com/center-for-threat-informed-defense/adversary_emulation_library data/adversary-emulation-plans
RUN mkdir -p data/adversary-emulation-plans/payloads && \
    cp -v payloads/wmiexec.vbs data/adversary-emulation-plans/payloads/wmiexec.vbs && \
    cp -v payloads/dnscat2.ps1 data/adversary-emulation-plans/payloads/dnscat2.ps1 && \
    cp -v payloads/psexec.exe data/adversary-emulation-plans/payloads/psexec.exe && \
    cp -v payloads/wce.exe data/adversary-emulation-plans/payloads/wce.exe && \
    cp -v payloads/netsess.exe data/adversary-emulation-plans/payloads/netsess.exe && \
    cp -v payloads/tcping.exe data/adversary-emulation-plans/payloads/tcping.exe && \
    cp -v payloads/putty.exe data/adversary-emulation-plans/payloads/putty.exe && \
    cp -v payloads/secretsdump.exe data/adversary-emulation-plans/payloads/secretsdump.exe && \
    cp -v payloads/nbtscan.exe data/adversary-emulation-plans/payloads/nbtscan.exe && \
    cp -v payloads/PsExec.exe data/adversary-emulation-plans/payloads/PsExec.exe && \
    cp -v payloads/psexec_sandworm.py data/adversary-emulation-plans/payloads/psexec_sandworm.py && \
    cp -v payloads/adfind.exe data/adversary-emulation-plans/payloads/adfind.exe && \
    cp -v data/adversary-emulation-plans/apt29/Archive/CALDERA_DIY/evals/payloads/m.exe data/adversary-emulation-plans/payloads/m64.exe && \
    cp -v data/adversary-emulation-plans/apt29/Archive/CALDERA_DIY/evals/payloads/m.exe payloads/m64.exe && \
    unzip -P malware data/adversary-emulation-plans/wizard_spider/Resources/control_server/files/dumpWebBrowserCreds.exe.zip -d payloads/ && \
    unzip -P malware data/adversary-emulation-plans/wizard_spider/Resources/control_server/files/dumpWebBrowserCreds.exe.zip -d data/adversary-emulation-plans/payloads/ && \
    unzip -P malware data/adversary-emulation-plans/wizard_spider/Resources/control_server/files/rubeus.exe.zip -d payloads/ && \
    unzip -P malware data/adversary-emulation-plans/wizard_spider/Resources/control_server/files/rubeus.exe.zip -d data/adversary-emulation-plans/payloads/ && \
    unzip -P malware data/adversary-emulation-plans/wizard_spider/Resources/Ryuk/bin/ryuk.exe.zip -d payloads/ && \
    unzip -P malware data/adversary-emulation-plans/wizard_spider/Resources/Ryuk/bin/ryuk.exe.zip -d data/adversary-emulation-plans/payloads/
RUN apt-get -y autoremove && \
    apt-get clean all && \
    rm -rf /var/lib/apt/lists/*
WORKDIR /usr/src/app