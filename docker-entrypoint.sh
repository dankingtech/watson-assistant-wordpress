#!/bin/sh
set -eux
cp -r /app/watson-conversation/* /app/dist
exec "$@"