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


SYMLINK="$BINPATH/tetl"

if [ -h "$SYMLINK" ] || [ -e "$SYMLINK" ]; then
  unlink $SYMLINK
fi


URL="http://tinyurl.com/gettetl"
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

ln -s "$TETL/stack/bin" $SYMLINK


echo "Installing"
exec $SYMLINK --install
echo "Done"
