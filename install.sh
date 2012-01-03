#!/bin/sh

BINPATH="/usr/local/bin"

if [ ! -d "$BINPATH" ]; then
  BINPATH="/usr/bin"
fi


SYMLINK="$BINPATH/atl"

if [ -h "$SYMLINK" ] || [ -e "$SYMLINK" ]; then
  unlink "$SYMLINK"
fi


echo "Creating symlink"

ln -s "$PWD/stack/app_console.sh" "$SYMLINK"

chmod +x "$SYMLINK"

"$SYMLINK" --install

echo "Done"
