#!/bin/bash

rm -rf /root/.vnc/*.pid /root/.vnc/*.log /tmp/.X1* /run/dbus/pid
mkdir -p /root/.vnc/
touch /root/.Xauthority
vncpasswd -f <<< $VNC_PASSWORD > /root/.vnc/passwd
vncserver -PasswordFile /root/.vnc/passwd
(echo "$VNC_PASSWORD" && echo "$VNC_PASSWORD") | passwd 2> /dev/null
dbus-daemon --config-file=/usr/share/dbus-1/system.conf
sed -i "s/#PermitRootLogin prohibit-password/PermitRootLogin yes/g" /etc/ssh/sshd_config
sed -i "s/#ListenAddress 0.0.0.0/ListenAddress 0.0.0.0/g" /etc/ssh/sshd_config
yes | ssh-keygen -f /root/.ssh/id_rsa -P ""
cat /root/.ssh/id_rsa.pub > /root/.ssh/authorized_keys
/etc/init.d/ssh start
ssh -o "StrictHostKeyChecking no" root@10.0.0.100 -D 3128 -N -f
(python2.7 /script/tunell.py) &
/usr/share/novnc/utils/novnc_proxy --listen 80 --vnc 127.0.0.1:5901