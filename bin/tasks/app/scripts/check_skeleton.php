<?php

$name = array_shift($params);
$test = realpath(dirname($name));

if (! $name) {
  error("\n  Missing application name\n");
} elseif ( ! is_dir($test)) {
  error("\n  Can't create the directory\n");
} else {
  $app_path = path($test, basename($name));

  if ( ! arg('f force') && is_dir($app_path)) {
    error("\n  Directory '$name' already exists\n");
  } else {
    arg('D delete-all') && \IO\Dir::unfile($app_path, '*', TRUE);
    require path(__DIR__, 'create_application.php');
  }
}
