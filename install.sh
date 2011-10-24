#!/bin/sh

# Ubuntu only

URL="http://tinyurl.com/gettetl"
TETL="$HOME/.tetlphp"

mkdir -p "$TETL/tmp"
cd "$TETL/tmp"


echo "Downloading latest version"

curl -L $URL | tar xzf -

TMPPATH="$TETL/tmp/$(ls $TETL/tmp)"


echo "Configuring at $TETL"

cd "$TMPPATH"
cp -R . "$TETL"
rm -rf "$TETL/tmp"

cd $HOME


echo "Creating symlink"

SYMLINK="/usr/local/bin/tetl"

if [ -h "$SYMLINK" ] || [ -e "$SYMLINK" ]; then
  sudo unlink "$SYMLINK"
fi

sudo ln -s "$TETL/console/bin" "$SYMLINK"


echo "Installing"
sudo "$SYMLINK" --install
echo "Done"
