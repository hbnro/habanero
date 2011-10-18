#!/bin/sh

if [ "$(uname -s)" != "Linux" ]; then
  echo "Linux only, apologies."
else
  if [ "$(whoami)" != "root" ]; then
    echo "Please run script as root (or sudo)."
  else
    TETL="$HOME/.tetlphp"
    mkdir -p "$TETL/tmp"
    cd "$TETL/tmp"


    echo "Downloading latest version."

    curl -L http://tinyurl.com/gettetl | tar xzf -

    TMPPATH="$TETL/tmp/$(ls $TETL/tmp)"


    echo "Configuring at $TETL"

    cd "$TMPPATH"
    cp -R . "$TETL"
    rm -rf "$TETL/tmp"


    cd $HOME


    echo "Creating symlink."

    SYMLINK="/usr/local/bin/tetl"

    if [ -h "$SYMLINK" ] || [ -d "$SYMLINK" ]; then
      echo "$SYMLINK"
    fi

    ln -s "$TETL/console/bin" $SYMLINK

    echo "Installing."
    sudo $SYMLINK --install
    echo "Done."
  fi
fi
