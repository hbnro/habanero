#!/bin/sh

if [ `whoami` != "root" ]; then
  sudo sh $0 $*
  exit 1
fi

if [ "$SUDO_USER" = "root" ]; then
  echo "Please run script with sudo"
  exit 1
fi


if [ ! -d "/Users" ]; then
  TETL="$HOME/.tetlphp"
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
