#!/bin/bash

usermod -u 1000 www-data
echo '* Starting nginx'
/usr/bin/supervisord -n -c /etc/supervisord.conf
