#!/bin/sh

if [ ! -d "/Users" ]; then
  TETL="$HOME/.local/share/phplib/tetlphp"
else
  TETL="$HOME/Library/PHP/tetlphp"
fi


BINPATH="/usr/local/bin"

if [ ! -d "$BINPATH" ]; then
  BINPATH="/usr/bin"
fi


SYMLINK="$BINPATH/atl"


echo "Uninstalling"
exec $SYMLINK --uninstall


echo "Removing symlink"


if [ -h "$SYMLINK" ] || [ -e "$SYMLINK" ]; then
  unlink $SYMLINK
fi


echo "Removing framework files"

if [ -e "$TETL" ]; then
  rm -rf $TETL
fi

echo "Done"
