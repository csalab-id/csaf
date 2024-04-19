FROM cgr.dev/chainguard/go:latest as build
RUN CGO_ENABLED=1 go install github.com/kitabisa/teler-proxy/cmd/teler-proxy@latest

FROM cgr.dev/chainguard/wolfi-base:latest
LABEL maintainer="admin@csalab.id"
COPY --from=build /root/go/bin/teler-proxy /teler-proxy
ENTRYPOINT [ "/teler-proxy" ]