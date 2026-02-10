FROM golang:1.25-alpine AS builder
RUN apk add --no-cache gcc musl-dev sqlite-dev
WORKDIR /build
COPY data/locker/go.mod data/locker/go.sum ./
RUN go mod download
COPY data/locker/*.go ./
RUN CGO_ENABLED=1 GOOS=linux go build web.go common.go
RUN CGO_ENABLED=1 GOOS=linux go build encrypt.go common.go
RUN CGO_ENABLED=1 GOOS=linux go build decrypt.go common.go

FROM alpine:latest
LABEL maintainer="admin@csalab.id"
RUN apk --no-cache add ca-certificates sqlite-libs
WORKDIR /app
COPY --from=builder /build/web .
COPY --from=builder /build/encrypt .
COPY --from=builder /build/decrypt .
RUN mkdir -p /app/data
VOLUME [ "/app/data" ]
EXPOSE 80
CMD ["./web"]
