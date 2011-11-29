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

if [ -h "$SYMLINK" ] || [ -e "$SYMLINK" ]; then
  unlink $SYMLINK
fi

#TODO: please use the real URL, dont be hackish dude!
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

ln -s "$TETL/stack/app_console.sh" $SYMLINK

chmod +x $SYMLINK


echo "Installing"
exec $SYMLINK --install
echo "Done"
