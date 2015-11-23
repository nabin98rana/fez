#!/bin/bash

set -e
TMPFS_DIR=/opt/tmpfs

echo "none            ${TMPFS_DIR}  tmpfs   defaults,size=1000M,uid=999,gid=999,mode=0700          0       0" >> /etc/fstab

mkdir ${TMPFS_DIR} && chown mysql: ${TMPFS_DIR}
mount ${TMPFS_DIR}

source /entrypoint.sh
