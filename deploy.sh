#!/bin/bash
rsync . steempress:/var/www/steempress --rsh ssh --recursive --perms --delete --verbose --exclude=.git* --checksum -a
