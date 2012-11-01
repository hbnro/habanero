<?php

pretty(function () {
    $trap = function () {
        if (is_file(func_get_arg(0))) {
          $test = include func_get_arg(0);

          is_array($test) && extract($test);

          unset($test);
        }
        return isset($config) ? $config : get_defined_vars();
      };


    $what = 'current';

    if (arg('dev')) {
      $what = 'development';
      $file = path(APP_PATH, 'config', 'environments', "$what.php");
    } elseif (arg('prod')) {
      $what = 'production';
      $file = path(APP_PATH, 'config', 'environments', "$what.php");
    } elseif (arg('app')) {
      $what = 'application';
      $file = path(APP_PATH, 'config', "$what.php");
    } elseif (arg('global')) {
      $file = path(APP_PATH, 'config.php');
      $what = 'default';
    }

    info("# $what");

    $config = isset($file) ? $trap($file) : config();

    $vars = array_slice(flags(), 1);
    $vars = array_diff_key($vars, array_flip(array('global', 'app', 'dev', 'prod')));

    if ( ! empty($vars)) {
      success("Configuration for $what updated");
      print_r($vars);

      $code = '';

      foreach ($vars as $item => $value) {
        $sub = explode('.', $item);
        $key = "['" . join("']['", $sub) . "']";

        $value = trim(var_export($value, TRUE));
        $value = is_numeric($value) ? substr($value, 1, -1) : $value;

        $code .= "\$config{$key} = $value;\n";
      }

      if (isset($file)) {
        ! is_file($file) && mkdir(dirname($file), 0777, TRUE) && write($file, "<?php\n\n");
        write($file, $code, TRUE);
      }
    } else {
      print_r($config);
    }
  });
