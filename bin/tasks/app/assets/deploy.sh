#!/bin/sh
rsync -avzC --delete --progress --exclude-from exclude.txt --stats -e 'ssh' . root@0.0.0.0:/var/www/domain.tld
