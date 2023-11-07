FROM python:3.11.3-slim-buster
LABEL maintainer="admin@csalab.id"
WORKDIR /usr/src/app
ENV PYTHONDONTWRITEBYTECODE 1
ENV PYTHONUNBUFFERED 1
RUN apt-get update && \
apt-get install -y --no-install-recommends netcat git && \
apt-get clean all && \
rm -rf /var/lib/apt/lists/*
RUN git clone https://github.com/CyberCX-STA/PurpleOps.git /usr/src/app && \
python3 -m pip install --no-cache-dir --upgrade pip~=23.3 && \
python3 -m pip install --no-cache-dir --requirement requirements.txt
ENTRYPOINT ["/usr/src/app/entrypoint.sh"]