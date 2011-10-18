<?php

$vhost_path = '/etc/apache2/sites-available';

$test = array();

foreach (dir2arr($vhost_path) as $one) {
  $test []= basename($one);
}

$hosts_file = '/etc/hosts';

$list = array();

foreach (file($hosts_file) as $line) {
  if ( ! trim($line)) {
    continue;
  }

  @list($key, $val) = preg_split('/\s+/', $line);

  if (isset($list[$key])) {
    ! is_array($list[$key]) && $list[$key] = (array) $list[$key];
    $list[$key] []= $val;
  } else {
    $list[$key] = $val;
  }
}




info(CWD);

echo yes('sure?') ? 'Y' : 'N';
/*

$app = basename(CWD);

if (in_array($app, $test)) {
// TODO: confirm?
  notice('Updating configuration.');
} else {
  success('Creating configuration.');
}


dump($test,1);
dump($list,1);*/
