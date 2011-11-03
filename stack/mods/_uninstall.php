<?php

info('Looking for php configuration');

$paths = array(
  '/private/etc',
  '/etc/php5/apache2',
);


foreach ($paths as $one) {
  if (is_file("$one/php.ini")) {
    $ini_file = "$one/php.ini";
    break;
  }
}

if (empty($ini_file)) {
  error('Not found a suitable php.ini file on your system!');
} else {
  uninstall_from($ini_file);
}

bold('Done');


function uninstall_from($php_ini) {
  $config = read($php_ini);
  $test   = preg_replace('/^\s*include_path.*?;;\s*$/m', '', $config);

  if ($test <> $config) {
    success('Updating include_path');
    write($php_ini, $test);

    sleep(1);


    $apache_bin = '/etc/init.d/apache2';

    ! is_file($apache_bin) && $apache_bin = 'apachectl';

    system("$apache_bin restart");
  } else {
    notice('Without changes');
  }
}

