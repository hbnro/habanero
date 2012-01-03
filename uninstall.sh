#!/bin/sh

BINPATH="/usr/local/bin"

if [ ! -d "$BINPATH" ]; then
  BINPATH="/usr/bin"
fi


SYMLINK="$BINPATH/atl"

echo "Removing symlink"

"$SYMLINK" --uninstall

if [ -h "$SYMLINK" ] || [ -e "$SYMLINK" ]; then
  unlink "$SYMLINK"
fi

echo "Done"
