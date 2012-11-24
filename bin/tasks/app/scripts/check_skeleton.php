<?php

@list($name) = $params;

if ( ! $name) {
  error("\n  Missing application name\n");
} else {
  $app_path = path(APP_PATH, $name);

  if ( ! arg('f', 'force') && is_dir($app_path)) {
    error("\n  Directory '$name' already exists\n");
  } else {
    arg('D', 'delete-all') && \IO\Dir::unfile($app_path, '*', TRUE);
    require path(__DIR__, 'create_application.php');
  }
}
