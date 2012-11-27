<?php

$name = array_shift($params);

if ( ! $name OR is_bool($name) OR (strpos($name, ':') !== FALSE)) {
  error("\n  Missing model name\n");
} else {
  $model_file = path(APP_PATH, 'app', 'models', "$name.php");
  $model_class = arg('c', 'class') ?: camelcase($name, TRUE, '\\');

  if ( ! is_file($model_file)) {
    error("\n  Missing '$name' model\n");
  } else {

    $fail = FALSE;
    $fields = array();
    $columns = $model_class::columns();

    if ( ! empty($params)) {
      foreach ($params as $one) {
        if (strpos($one, ':')) {
          @list($key, $type) = explode(':', $one);

          if ( ! isset($columns[$key])) {
            $fail = TRUE;
            error("\n  Unknown '$key' field\n");
          } elseif ( ! ($test = field_for($type ?: $columns[$key]['type'], $key))) {
            $fail = TRUE;
            error("\n  Unknown '$key:$type' field type\n");
          } else {
            $fields[$key] = $test;
          }
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


    if ( ! $fail) {
      if ( ! $fields) {
        error("\n  Missing fields for '$name' crud\n");
      } else {
        require path(__DIR__, 'build_scaffold.php');
      }
    }
  }
}
