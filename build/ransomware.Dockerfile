FROM golang:1.25 AS builder
RUN apt-get update && apt-get install -y --no-install-recommends \
    gcc \
    libc6-dev \
    libsqlite3-dev \
    && rm -rf /var/lib/apt/lists/*
WORKDIR /build
COPY data/locker/go.mod data/locker/go.sum ./
RUN go mod download
COPY data/locker/*.go ./
RUN CGO_ENABLED=1 GOOS=linux go build web.go common.go
RUN CGO_ENABLED=1 GOOS=linux go build encrypt.go common.go
RUN CGO_ENABLED=1 GOOS=linux go build decrypt.go common.go

FROM debian:bookworm-slim
LABEL maintainer="admin@csalab.id"
RUN apt-get update && apt-get install -y --no-install-recommends \
    ca-certificates \
    libsqlite3-0 \
    && rm -rf /var/lib/apt/lists/*
WORKDIR /app
COPY --from=builder /build/web .
COPY --from=builder /build/encrypt .
COPY --from=builder /build/decrypt .
RUN mkdir -p /app/data
VOLUME [ "/app/data" ]
EXPOSE 80
CMD ["./web"]
