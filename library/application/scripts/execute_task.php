<?php

@list($name, $key) = explode(':', $name);

if ( ! $name) {
  error(ln("app.missing_script_name"));
} else {
  info(ln('app.verifying_script'));

  if (is_file($script_file = APP_PATH.DS.$name.EXT)) {
    success(ln('app.executing_script', array('path' => str_replace(APP_PATH.DS, '', $script_file))));
    require $script_file;
    done();
    exit;
  }

  $task_file = APP_PATH.DS.'tasks'.DS.$name.DS.'initialize'.EXT;
  $path      = str_replace(APP_PATH.DS, '', $task_file);

  if ( ! is_file($task_file)) {
    error(ln('app.missing_script_file', array('name' => $path)));
  } else {
    $task_class = "{$name}_task";

    /**#@+
     * @ignore
     */
    require $task_file;
    /**#@-*/

    $task_class::defined('init') && $task_class::init();

    ! $key && $key = ! empty($task_class::$default) ? $task_class::$default : 'main';


    if ( ! class_exists($task_class)) {
      error(ln('app.missing_task_class', array('path' => $path)));
    } elseif ( ! $task_class::defined($key)) {
      error(ln('app.unknown_task_param', array('name' => $key)));
    } else {
      success(ln('app.executing_task', array('name' => $name, 'param' => $key)));
      $task_class::$key();
      done();
    }
  }
}

/* EOF: ./library/application/scripts/execute_task.php */
