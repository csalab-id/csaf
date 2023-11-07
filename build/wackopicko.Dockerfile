FROM adamdoupe/wackopicko:latest
LABEL maintainer="admin@csalab.id"
WORKDIR /var/www/html
# Privilege escalation scenario
RUN chmod +s /usr/bin/mawk