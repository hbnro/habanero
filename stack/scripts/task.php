<?php

$ns = array_shift($args);

if ( ! $ns) {
  error(ln('missing_script_name'));
} else {
  info(ln('verifying_namespace', array('name' => $ns)));

  $path = APP_PATH.DS.'tasks'.DS.$ns;


  if ( ! app_generator::exists($ns)) {
    if (cli::flag('php')) {
      write($path.EXT, '<' . "?php\nnotice('Hello $ns task!');\n");
    } else {
      $args []= 'default';

      mkpath($path);
      write($path.DS.'config'.EXT, '<' . "?php\n\$option = 'value';");
      write($path.DS.'initialize'.EXT, '<' . "?php\n");
    }

    notice(ln('creating_script', array('name' => $ns)));
  } else {
    notice(ln('script_exists', array('name' => $ns)));
  }

  if ( ! empty($args)) {
    foreach ($args as $one) {
      if (app_generator::exists($ns, $one)) {
        error(ln('task_exists', array('command' => $one)));
      } elseif (is_file($script = $path.DS.'initialize'.EXT)) {
        write($script, "\ntask('$ns:$one', array(\n  'desc' => 'The $one task'),\n  'exec' => function (\$config) {},\n));\n", 1);
        success(ln('creating_task', array('command' => $one)));
      }
    }
  }

  done();
}

/* EOF: ./stack/scripts/task.php */
