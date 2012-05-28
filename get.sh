#!/bin/sh

TETL="$HOME/.php/tetlphp"
BINPATH="/usr/local/bin"

if [ ! -d "$BINPATH" ]; then
  BINPATH="/usr/bin"
fi

SYMLINK="$BINPATH/atl"

if [ -f "$SYMLINK" ]; then
  echo "Already installed"
  exit 0
fi

URL="https://nodeload.github.com/pateketrueke/tetlphp/tarball/master"
mkdir -p "$TETL/tmp"
cd "$TETL/tmp"

echo "Downloading latest version..."

curl --silent --max-time 60 --location $URL | tar xzf -
TMPPATH="$TETL/tmp/$(ls $TETL/tmp)"

echo "Configuring at $TETL"

cd "$TMPPATH"
cp -R . "$TETL"
rm -rf "$TETL/tmp"

echo "Creating symlink"

ln -s "$TETL/stack/app_console.sh" $SYMLINK > /dev/null 2>&1

chmod +x $SYMLINK

echo "Installing..."
$SYMLINK --install > /dev/null 2>&1
echo "Done"
