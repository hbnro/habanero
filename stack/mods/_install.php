<?php

$path = dirname(dirname(LIB));

$php_ini = '/etc/php5/apache2/php.ini';


info("Looking for $php_ini");

$config = read($php_ini);


preg_match_all('/^\s*include_path.*?;;\s*$/m', $config, $last);

$include_path = explode(PATH_SEPARATOR, get_include_path());
$include_path += (array) option('import_path', array());

if ( ! in_array($path, $include_path)) {
  $include_path []= $path;
}

$include_path = array_unique($include_path);
$property     = sprintf('include_path = "%s" ;;', join(PATH_SEPARATOR, $include_path));
$older        = trim(end($last[0]));

if ($older <> $property) {
  success('Updating include_path.');

  $mark = 'http://php.net/include-path';

  ! strstr($config, $mark) && $mark = '[PHP]';

  write($php_ini, str_replace($mark, "$mark\n$property", $config));

  sleep(1);
  system('/etc/init.d/apache2 restart');
} else {
  notice('Without changes.');
}

bold('Done.');
