#!/bin/bash

mkdir -p ~/.vnc/
rm -rf ~/.vnc/*.pid ~/.vnc/*.log /tmp/.X1*
touch ~/.Xauthority
vncpasswd -f <<< $VNC_PASSWORD > ~/.vnc/passwd
(echo "$VNC_PASSWORD";echo "$VNC_PASSWORD") | sudo passwd kali
vncserver -PasswordFile ~/.vnc/passwd
sudo dbus-daemon --config-file=/usr/share/dbus-1/system.conf
yes | ssh-keygen -f ~/.ssh/id_rsa -P ""
cat ~/.ssh/id_rsa.pub > ~/.ssh/authorized_keys
sudo /etc/init.d/ssh start
ssh -o "StrictHostKeyChecking no" kali@localhost -D 3128 -N -f
(python2.7 /src/tunell.py) &
/usr/share/novnc/utils/novnc_proxy --listen 80 --vnc 127.0.0.1:5901