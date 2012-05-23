<?php

$name = cli::flag('run');

! is_string($name) && $name = '';

@list($name, $key) = explode(':', $name);

if ( ! $name) {
  error(ln('missing_script_name'));
  info(ln('available_tasks'));

  if ($test = findfile(APP_PATH.DS.'tasks', 'initialize'.EXT, TRUE)) {
    foreach ($test as $task_file) {
      /**
       * @ignore
       */
      require $task_file;
    }
  }

  app_generator::all();
  done();
} else {
  info(ln('verifying_script'));

  if (is_file($script_file = APP_PATH.DS.'tasks'.DS.$name.EXT)) {
    success(ln('executing_script', array('path' => str_replace(APP_PATH.DS, '', $script_file))));
    require $script_file;
    done();
    exit;
  }

  $task_file = APP_PATH.DS.'tasks'.DS.$name.DS.'initialize'.EXT;
  $path      = str_replace(APP_PATH.DS, '', $task_file);

  if ( ! is_file($task_file)) {
    error(ln('missing_script_file', array('name' => $path)));
  } else {
    /**#@+
     * @ignore
     */
    require $task_file;
    /**#@-*/


    $args = array($name);
    $key && $args []= $key;

    success(ln('executing_task', array('command' => sprintf('%s%s', $name, $key ? "#$key" : ''))));
    app_generator::apply('run', $args);
    done();
  }
}

/* EOF: ./stack/scripts/run.php */
