FROM owasp/modsecurity-crs:apache@sha256:f2523fbe1bc500399901f5e18ddd1b3cf1a653d7bfae4f12e80a077f3256dee5
LABEL maintainer="admin@csalab.id"
USER root
RUN sed -i '4d' /docker-entrypoint.sh