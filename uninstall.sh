#!/bin/sh

# Ubuntu only

TETL="$HOME/.tetlphp"

echo "Removing framework files"

if [ -e "$TETL" ]; then
  rm -rf "$TETL"
fi


echo "Removing symlink"

SYMLINK="/usr/local/bin/tetl"

if [ -h "$SYMLINK" ] || [ -e "$SYMLINK" ]; then
  sudo unlink "$SYMLINK"
fi


echo "Uninstalling"
sudo "$SYMLINK" --uninstall
echo "Done"
