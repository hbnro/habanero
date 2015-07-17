#!/bin/sh

# sudo
  if [ `whoami` != 'root' ]; then
    echo "Run \`$0 $*\` with sudo" || exit 1
    exit 0
  fi


# binaries
  PHPBIN="$(which php)"
  BINPATH="$(dirname $PHPBIN)"

  if [ -z "$($PHPBIN -v)" ]; then
    echo "PHP not found"
    exit 1
  fi


# version
  VTEST=$(php -r "echo version_compare(PHP_VERSION, '5.3.19') >= 0?1:NULL;")

  if [ -z "$VTEST" ]; then
    echo "PHP >= 5.3.19 is needed"
    exit 1
  fi


# include_path
  SHARED="/usr/share/php"

  echo "Using $SHARED as include_path"
  echo "- Please configure it manually in your php.ini"

  INCPATH="$SHARED/habanero"

  if [ -d "$INCPATH" ]; then
    rm -rf "$INCPATH"
  fi


# dependencies
  if [ ! -d "$PWD/vendor" ]; then
    COMPOSER_URL="http://getcomposer.org/composer.phar"

    if [ ! -f "$BINPATH/composer" ]; then
      printf "\rDownloading the composer... "

      curl --max-time 60 -sL $COMPOSER_URL > $BINPATH/composer
      chmod +x $BINPATH/composer
      echo 'done'
    fi

    echo "- Please run \`composer install --prefer-source\` manually"
  fi


# setup
  SYMLINK="$BINPATH/hs"

  if [ -f "$SYMLINK" ]; then
    unlink "$SYMLINK"
  fi

  echo "Configuring at $INCPATH"

  ln -s . $INCPATH

  echo "#!/bin/sh\nphp $INCPATH/bin/initialize.php -- \"\$@\"" > $SYMLINK
  chmod +x $SYMLINK

  echo "Created symlink at $SYMLINK"

  echo 'Ready'


# exit
  sudo -k
