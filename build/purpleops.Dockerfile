FROM python:3.11.3-slim-buster
LABEL maintainer="admin@csalab.id"
WORKDIR /usr/src/app
ENV PYTHONDONTWRITEBYTECODE 1
ENV PYTHONUNBUFFERED 1
RUN apt-get update && apt-get install -y netcat git
RUN git clone https://github.com/CyberCX-STA/PurpleOps.git /usr/src/app
RUN pip install --upgrade pip
RUN pip install -r requirements.txt
ENTRYPOINT ["/usr/src/app/entrypoint.sh"]