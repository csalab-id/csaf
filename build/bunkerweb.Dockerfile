FROM bunkerity/bunkerweb-all-in-one:1.6.6
LABEL maintainer="admin@csalab.id"
COPY ./config/bunkerweb/variables.env /etc/nginx/variables.env