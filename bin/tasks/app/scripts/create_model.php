<?php

$name = array_shift($params);

if ( ! $name OR (strpos($name, ':') !== FALSE)) {
  error("\n  Missing model name\n");
} else {
  if ( ! $params) {
    error("\n  Missing columns for '$name' model\n");
  } else {

    $out_file = path(APP_PATH, 'models', "$name.php");

    if ( ! is_file($out_file) OR arg('force')) {
      $fields = array();

      foreach ($params as $i => $one) {
        if (strpos($one, ':')) {
          @list($field, $type) = explode(':', $one);
          $fields[$field] = compact('type');
        }
      }

      $table = arg('table');
      $extends = arg('extends') ?: 'database';
      $conn = arg('connection') ?: 'default';
      $idx = explode(',', arg('indexes'));

      add_model($name, $table, $fields, $idx, $extends, $conn);
    } else {
      error("\n  Model '$name' already exists\n");
    }
  }
}
