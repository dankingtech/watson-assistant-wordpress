#!/usr/bin/env bash
set -eux
SCRIPT_DIR=$(cd $(dirname "$0"); pwd); pushd "$SCRIPT_DIR"
export IMAGE_NAME="watsonconversation-test"
# build image
pushd ..
docker build -t ${IMAGE_NAME} .
popd
# extract plugin from the image
rm -rf ./plugin/ || true
mkdir ./plugin/
docker run --rm -ti -v ${PWD}/plugin:/app/dist ${IMAGE_NAME}
popd
