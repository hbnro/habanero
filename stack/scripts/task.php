<?php

$ns = array_shift($args);

if ( ! $ns) {
  error(ln('missing_script_name'));
} else {
  info(ln('verifying_namespace', array('name' => $ns)));

  $path = APP_PATH.DS.'tasks'.DS.$ns;


  if ( ! app_generator::exists($ns)) {
    if (cli::flag('php')) {
      write($path.EXT, '<' . "?php\necho 'Hello task!';\n");
    } else {
      mkpath($path);
      write($path.DS.'config'.EXT, '<' . "?php\n\$option = 'value';");
      write(mkpath($path.DS.'locale').DS.'en'.EXT, '<' . "?php\n\$lang['default_title'] = 'The $ns description';\n");
      write($path.DS.'initialize'.EXT, '<' . "?php\ni18n::load_path(__DIR__.DS.'locale', '$ns');\n");
    }

    notice(ln('creating_script', array('name' => $ns)));
    $args []= 'default';
  }

  if ( ! empty($args)) {
    foreach ($args as $one) {
      if (app_generator::exists($ns, $one)) {
        error(ln('task_exists', array('command' => $one)));
      } elseif (is_file($script = $path.DS.'initialize'.EXT)) {
        write($script, "\napp_generator::task('$ns:$one', array(\n  'desc' => ln('$ns.{$one}_title'),\n  'exec' => function (\$config) {},\n));\n", 1);
        success(ln('creating_task', array('command' => $one)));
      }
    }
  } else {
    error(ln('missing_arguments'));
  }

  done();
}

/* EOF: ./stack/scripts/task.php */
