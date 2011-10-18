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

bold('Done');
