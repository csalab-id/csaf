FROM alpine:3.17.1
RUN apk update && \
apk upgrade && \
apk add make git gcc musl-dev pcre-dev libevent-dev openssl-dev && \
git clone https://github.com/CoolerVoid/OctopusWAF && \
cd OctopusWAF && \
make