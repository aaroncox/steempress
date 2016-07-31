#!/bin/bash

echo '* Working around permission errors locally by making sure that "nginx" uses the same uid and gid as the host volume'
TARGET_UID=$(stat -c "%u" /var/lib/nginx)
echo '-- Setting nginx user to use uid '$TARGET_UID
usermod -o -u $TARGET_UID nginx || true
TARGET_GID=$(stat -c "%g" /var/lib/nginx)
echo '-- Setting nginx group to use gid '$TARGET_GID
groupmod -o -g $TARGET_GID nginx || true
echo '* Starting nginx'
/usr/bin/supervisord -n -c /etc/supervisord.conf
