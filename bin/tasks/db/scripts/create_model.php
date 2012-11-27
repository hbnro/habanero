<?php

$name = array_shift($params);

if ( ! $name OR is_bool($name) OR (strpos($name, ':') !== FALSE)) {
  error("\n  Missing model name\n");
} else {
  $fields = array();

  foreach ($params as $i => $one) {
    if (strpos($one, ':')) {
      @list($field, $type) = explode(':', $one);
      $fields[$field] = compact('type');
    }
  }

  if ( ! $fields) {
    error("\n  Missing columns for '$name' model\n");
  } else {
    $out_file = path(APP_PATH, 'app', 'models', "$name.php");

    if (arg('t timestamps')) {
      $fields['created_at']  =
      $fields['modified_at'] = array('type' => 'timestamp');
    }

    if ( ! is_file($out_file) OR arg('f force')) {
      $table = arg('n table');
      $extends = arg('x extends') ?: 'database';
      $conn = arg('c connection') ?: 'default';
      $idx = array_filter(explode(',', arg('i indexes')));

      add_model($name, $table, $fields, $idx, $extends, $conn);
    } else {
      error("\n  Model '$name' already exists\n");
    }
  }
}
