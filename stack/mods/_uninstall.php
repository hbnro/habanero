<?php

info('Looking for php configuration');

$test  = `php-config`;
$regex = array(
          '/--with-config-file-path=(\S+)/',
          '/--sysconfdir=(\S+)/',
        );

foreach ($regex as $one) {
  if (preg_match($one, $test, $match)) {
    if (is_file($ini_file = "$match[1]/php.ini")) {
      uninstall_from($ini_file);
      break;
    }
  }
}

if (empty($ini_file)) {
  error('Not found a suitable php.ini file on your system!');
  exit;
}


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


  $hosts_file = '/etc/hosts';

  info("Looking for $hosts_file");

  $config = read($hosts_file);
  $test   = preg_replace('/^\s*127\.0\.0\.1\s+[\w+.-]+\s*##\s*$/m', '', $config);

  if ($config <> $test) {
    success("Updating $hosts_file");
    write($hosts_file, $test);
  } else {
    notice('Without changes');
  }
}

bold('Done');
