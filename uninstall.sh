#!/bin/sh

if [ "$(uname -s)" != "Linux" ]; then
  echo "Linux only, apologies."
else
  if [ "$(whoami)" != "root" ]; then
    echo "Please run script as root (or sudo)."
  else
    echo ":) Good bye dude."

    TETL="$HOME/.tetlphp"

    if [ -e "$TETL" ]; then
      rm -rf "$TETL"
    fi

    echo "Removing symlink."

    SYMLINK="/usr/local/bin/tetl"

    if [ -h "$SYMLINK" ] || [ -e "$SYMLINK" ]; then
      unlink "$SYMLINK"
    fi

    echo "Uninstalling."
    exec $SYMLINK --uninstall

    echo "Done."
  fi
fi
