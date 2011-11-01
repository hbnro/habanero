<?php

info('Looking for php configuration');

$test  = `php-config`;
$regex = array(
          '/--with-config-file-path=(\S+)/',
          '/--sysconfdir=(\S+)/'
        );

foreach ($regex as $one) {
  if (preg_match($one, $test, $match)) {
    install_to("$match[1]/php.ini");
    break;
  }
}


function install_to($php_ini) {
  $config = read($php_ini);
  $path   = dirname(dirname(LIB));


  preg_match_all('/^\s*include_path.*?;;\s*$/m', $config, $last);

  $include_path = explode(PATH_SEPARATOR, get_include_path());

  if ( ! in_array($path, $include_path)) {
    $include_path []= $path;
  }

  $include_path = array_unique($include_path);
  $property     = sprintf('include_path = "%s" ;;', join(PATH_SEPARATOR, $include_path));
  $older        = trim(end($last[0]));

  if ($older <> $property) {
    success('Updating include_path');

    if (preg_match('/\s*include_path\s*=\s*"(.+?)"/m', $config, $match)) {
      $mark = $match[0];
    } else {
      $mark = 'http://php.net/include-path';

      ! strstr($config, $mark) && $mark = '[PHP]';
    }

    write($php_ini, str_replace($mark, "$mark\n$property", $config));

    sleep(1);


    $apache_bin = '/etc/init.d/apache2';

    ! is_file($apache_bin) && $apache_bin = 'apachectl';

    system("$apache_bin restart");
  } else {
    notice('Without changes');
  }
}

bold('Done');
