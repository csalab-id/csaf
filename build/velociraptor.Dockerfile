FROM wlambert/velociraptor:0.75.6
LABEL maintainer="admin@csalab.id"
COPY --chown=root:root --chmod=755 script/velociraptor.startup.sh /entrypoint