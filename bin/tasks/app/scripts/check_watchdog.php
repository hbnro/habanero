<?php

$name = array_shift($params);
$test = realpath(dirname($name));

if (! $name) {
  error("\n  Missing application name\n");
} else {
  $recreate = FALSE;
  $target_dir = path($test, basename($name));

  if (arg('c create')) {
    if ( ! arg('f force') && is_dir($target_dir)) {
      return error("\n  Directory '$name' already exists\n");
    }
    $recreate = TRUE;
  } elseif ( ! is_dir($target_dir)) {
    return error("\n  Directory '$name' does not exists\n");
  }
  require path(__DIR__, 'run_watchdog.php');
}
