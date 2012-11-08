<?php

@list($name) = $params;

if ( ! $name) {
  error("\n  Missing application name\n");
} else {
  $app_path = path(APP_PATH, $name);

  if (empty($params['force']) && is_dir($app_path)) {
    error("\n  Directory '$name' already exists\n");
  } else {
    require path(__DIR__, 'create_application.php');
  }
}
