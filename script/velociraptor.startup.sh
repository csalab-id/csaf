#!/bin/bash
set -e
CLIENT_DIR="/velociraptor/clients"

# Move binaries into place
cp /opt/velociraptor/linux/velociraptor . && chmod +x velociraptor
mkdir -p $CLIENT_DIR/linux && rsync -a /opt/velociraptor/linux/velociraptor /velociraptor/clients/linux/velociraptor_client
mkdir -p $CLIENT_DIR/mac && rsync -a /opt/velociraptor/mac/velociraptor_client /velociraptor/clients/mac/velociraptor_client
mkdir -p $CLIENT_DIR/windows && rsync -a /opt/velociraptor/windows/velociraptor_client* /velociraptor/clients/windows/

# Re-generate client config in case server config changed
./velociraptor --config server.config.yaml config client > client.config.yaml
./velociraptor --config server.config.yaml user add admin "$VELOX_PASSWORD" --role administrator

# Repack clients
./velociraptor config repack --exe clients/linux/velociraptor_client client.config.yaml clients/linux/velociraptor_client_repacked
./velociraptor config repack --exe clients/mac/velociraptor_client client.config.yaml clients/mac/velociraptor_client_repacked
./velociraptor config repack --exe clients/windows/velociraptor_client.exe client.config.yaml clients/windows/velociraptor_client_repacked.exe
./velociraptor config repack --msi clients/windows/velociraptor_client.msi client.config.yaml clients/windows/velociraptor_client_repacked.msi

# Start Velocoraptor
./velociraptor --config server.config.yaml frontend -v