<?php

$php_ini = '/etc/php5/apache2/php.ini';


info("Looking for $php_ini");

$config = read($php_ini);
$test   = preg_replace('/^\s*include_path.*?;;\s*$/m', '', $config);

if ($test <> $config) {
  success('Updating include_path.');
  write($php_ini, $test);

  sleep(1);
  system('/etc/init.d/apache2 restart');
} else {
  notice('Without changes.');
}

bold('Done.');
