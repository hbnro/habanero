<?php

$name = array_shift($params);

if ( ! $name OR is_numeric($name) OR (strpos($name, ':') !== FALSE)) {
  error("\n  Missing model name\n");
} else {
  $name = singular($name);
  $model_file = path(APP_PATH, 'app', 'models', "$name.php");
  $model_class = arg('c class') ?: classify($name);

  if ( ! is_file($model_file) && ! arg('c class')) {
    error("\n  Missing '$name' model\n");
  } else {
    is_file($model_file) && require $model_file;

    $fail = FALSE;
    $fields = array();
    $columns = $model_class::columns();

    if ( ! empty($params)) {
      foreach ($params as $key) {
        @list($key, $type) = explode(':', $key);

        $type = $type ?: $columns[$key]['type'];

        if ( ! isset($columns[$key])) {
          $fail = TRUE;
          error("\n  Unknown '$key' field\n");
        } elseif ( ! ($test = field_for($type, $key))) {
          $fail = TRUE;
          error("\n  Unknown '$type' type on '$key' field\n");
        } else {
          $fields[$key] = $test;
        }
      }
    } else {
      foreach ($columns as $key => $val) {
        $fields[$key] = field_for($val['type'], $key);
      }
    }

    $pk = $model_class::pk();

    foreach (array($pk, 'created_at', 'modified_at') as $tmp) {
      if (isset($fields[$tmp])) {
        unset($fields[$tmp]);
      }
    }

    if (! $fail) {
      if (! $fields) {
        error("\n  Missing fields for '$name' crud\n");
      } else {
        require path(__DIR__, 'build_scaffold.php');
      }
    }
  }
}
