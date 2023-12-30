FROM adamdoupe/wackopicko:latest@sha256:76d1a9ad02ad7fb6bbc000eb12956f457087a0f5883bd23b6cc49d3051feae02
LABEL maintainer="admin@csalab.id"
WORKDIR /var/www/html
# Privilege escalation scenario
RUN chmod +s /usr/bin/mawk && \
    sed -i 's/volume of MySQL"/volume of MySQL"; \/create_mysql_admin_user.sh/g' /create_mysql_admin_user.sh && \
    sed -i "s/current.sql/\/current.sql/g" /create_mysql_admin_user.sh