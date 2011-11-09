<?php

cli::writeln(pretty(function () {
  $trap = function () {
    if (is_file(func_get_arg(0))) {
      $test = include func_get_arg(0);

      is_array($test) && extract($test);

      unset($test);
    }
    return isset($config) ? $config : get_defined_vars();
  };


  $what = 'current';

  if (cli::flag('dev')) {
    $what = 'development';
    $file = getcwd().DS.'config'.DS.'environments'.DS.$what.EXT;
  } elseif (cli::flag('prod')) {
    $what = 'production';
    $file = getcwd().DS.'config'.DS.'environments'.DS.$what.EXT;
  } elseif (cli::flag('app')) {
    $what = 'application';
    $file = getcwd().DS.'config'.DS.$what.EXT;
  } elseif (cli::flag('global')) {
    $file = getcwd().DS.'config'.EXT;
    $what = 'default';
  }

  info(ln("app.{$what}_configuration"));

  $config = isset($file) ? $trap($file) : config();

  $vars = array_slice(cli::args(), 1);
  $vars = array_diff_key($vars, array_flip(array('global', 'app', 'dev', 'prod')));

  if ( ! empty($vars)) {
    success(ln("app.setting_{$what}_options"));
    dump($vars, TRUE);

    $code = '';

    foreach ($vars as $item => $value) {
      $sub = explode('.', $item);
      $key = "['" . join("']['", $sub) . "']";

      $value = trim(var_export($value, TRUE));
      $value = is_num($value) ? substr($value, 1, -1) : $value;

      $code .= "\$config{$key} = $value;\n";
    }

    if (isset($file)) {
      ! is_file($file) && mkpath(dirname($file)) && write($file, "<?php\n\n");
      write($file, $code, 1);
    }
  } else {
    dump($config, TRUE);
  }
}));

done();

/* EOF: ./library/application/scripts/configuration.php */
