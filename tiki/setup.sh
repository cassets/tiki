#!/bin/sh

DIRS="backups dump img/wiki img/wiki_up modules/cache temp templates_c"
USER=yourlogin
GROUP=nobody

UNAME=`uname | cut -c 1-6`

if [ "$UNAME" = "CYGWIN" ];
then
	USER=SYSTEM
	GROUP=SYSTEM
fi

if [ -z "$1" ];
then
	cat <<EOF
Usage $0 user [group]

For example, if apache is running as user $USER, type:

  su -c '$0 $GROUP'

Alternatively, you may wish to set both the user and group:
  
  su -c '$0 $USER $GROUP'

This will allow you to delete certain files/directories without becoming root.
  
Or, if you can't become root, but are a member of the group apache runs under
(for example: $GROUP), you can type:

  $0 $USER $GROUP
  
EOF
exit 1
fi

for dir in $DIRS
do
	if [ ! -d $dir ]
	then
		mkdir -p $dir
	fi
done

chown -R $1 $DIRS

if [ -n "$2" ];
then
	chgrp -R $2 $DIRS
fi

chmod -R 02775 $DIRS
