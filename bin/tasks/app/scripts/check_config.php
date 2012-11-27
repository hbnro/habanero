<?php

$trap = function () {
    if (is_file(func_get_arg(0))) {
      $test = include func_get_arg(0);

      is_array($test) && extract($test);

      unset($test);
    }
    return isset($config) ? $config : get_defined_vars();
  };


$what = 'current';
$title = 'Loaded options';

if (arg('d dev')) {
  $what = 'development';
  $title = "Configuration for $what";
  $file = path(APP_PATH, 'config', 'environments', "$what.php");
} elseif (arg('p prod')) {
  $what = 'production';
  $title = "Configuration for $what";
  $file = path(APP_PATH, 'config', 'environments', "$what.php");
} elseif (arg('a app')) {
  $what = 'application';
  $title = 'Application options';
  $file = path(APP_PATH, 'config', "$what.php");
} elseif (arg('g global')) {
  $title = 'Main options';
  $file = path(APP_PATH, 'config.php');
  $what = 'default';
}


$config = isset($file) ? $trap($file) : config();
$params = array_diff_key(flags(), array_flip(array('global', 'app', 'dev', 'prod', 'd', 'p', 'a', 'g')));

if ( ! empty($params)) {
  success("\n  $title updated");

  $code = '';
  $params = array_merge($config, $params);

  foreach ($params as $item => $value) {
    if ( ! is_numeric($item)) {
      $sub = explode('.', $item);
      $key = "['" . join("']['", $sub) . "']";

      $value = var_export($value, TRUE);
      $code .= "\$config{$key} = $value;\n";
    }
  }

  if (isset($file)) {
    ! is_file($file) && mkdir(dirname($file), 0755, TRUE);
    write($file, "<?php\n$code\n");
  }
} else {
  info("\n  $title:");
}


$config = isset($file) ? $trap($file) : config();
printf("\n%s\n", preg_replace('/^/m', '  ', inspect($config)));
