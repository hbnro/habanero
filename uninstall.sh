#!/bin/sh

if [ `whoami` != "root" ]; then
  echo "Please run script as root (with sudo)"
  exit 1
fi

if [ $SUDO_USER = "root" ]; then
  echo "Please run script with sudo"
  exit 1
fi


if [ ! -d "/Users" ]; then
  TETL="$HOME/.tetlphp"
else
  TETL="$HOME/Library/PHP/tetlphp"
fi


echo "Removing framework files"

if [ -e "$TETL" ]; then
  rm -rf "$TETL"
fi


BINPATH="/usr/local/bin"

if [ ! -d "$BINPATH" ]; then
  BINPATH="/usr/bin"
fi


echo "Removing symlink"

SYMLINK="$BINPATH/tetl"

if [ -h "$SYMLINK" ] || [ -e "$SYMLINK" ]; then
  unlink "$SYMLINK"
fi


echo "Uninstalling"
exec $SYMLINK --uninstall
echo "Done"
