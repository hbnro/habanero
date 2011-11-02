<?php

i18n::load_path(__DIR__.DS.'locale', 'app');


class app_generator extends prototype
{

  final public static function st() {
    info(ln('app.verifying_installation'));

    if ( ! is_file(CWD.DS.'initialize'.EXT)) {
      error(ln('app.not_installed'));
    } else {
      $test  = dir2arr(CWD, '*', DIR_RECURSIVE | DIR_MAP);
      $count = sizeof($test);
      $size  = 0;

      foreach ($test as $file) {
        $size += filesize($file);
      }

      success(ln('app.counting_files', array('length' => number_format($count))));
      success(ln('app.sizing_files', array('size' => fmtsize($size))));
      success(ln('app.environment', array('env' => option('environment', 'unknown'))));

      bold(ln('tetl.done'));
    }
  }

  final public static function gen() {
    info(ln('app.verifying_installation'));

    if (is_file(CWD.DS.'initialize'.EXT) && ! cli::flag('force')) {
      notice(ln('app.application'));

      $tmp = dir2arr(CWD, '*', DIR_RECURSIVE | DIR_EMPTY);
      $map = function ($tree, $self, $deep = 0) {
        foreach ($tree as $key => $val) {
          $pre = str_repeat(' ', $deep);

          if (is_array($val)) {
            cli::writeln("$pre  \clight_gray,black($key)\c/");
            $self($val, $self, $deep + 2);
          } else {
            $size = fmtsize(filesize($val));
            $val  = basename($val);

            cli::writeln("$pre  \bwhite($val)\b \clight_gray($size)\c");
          }
        }
      };

      $map($tmp, $map);

      error(ln('app.directory_must_be_empty'));
    } else {
      require __DIR__.DS.'scripts'.DS.'create_application'.EXT;
    }

    bold(ln('tetl.done'));
  }

  final public static function make($what = '', $name = '') {
    config(CWD.DS.'config'.DS.'application'.EXT);

    if ( ! in_array($what, array(
      'controller',
      'action',
      'model',
    ))) {
      static::help();
    } else {
      info(ln('app.verifying_generator'));

      if ( ! $name) {
        error(ln("app.missing_{$what}_name"));
      } else {
        switch ($what) {
          case 'controller';
          case 'action';
          case 'model';
            require __DIR__.DS.'scripts'.DS."create_$what".EXT;;
          break;
          default;
          break;
        }
      }
    }

    bold(ln('tetl.done'));
  }

  final public static function conf() {
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
        $file = CWD.DS.'config'.DS.'environments'.DS.$what.EXT;
      } elseif (cli::flag('prod')) {
        $what = 'production';
        $file = CWD.DS.'config'.DS.'environments'.DS.$what.EXT;
      } elseif (cli::flag('app')) {
        $what = 'application';
        $file = CWD.DS.'config'.DS.$what.EXT;
      } elseif (cli::flag('global')) {
        $file = CWD.DS.'config'.EXT;
        $what = 'default';
      }

      info(ln("app.{$what}_configuration"));

      $config = isset($file) ? $trap($file) : config();

      $vars = array_slice(cli::args(), 1);
      $vars = array_diff_key($vars, array_flip(array('global', 'prod', 'test', 'dev', 'app', 'db')));

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

    bold(ln('tetl.done'));
  }

  final public static function run($name = '') {
    @list($name, $key) = explode(':', $name);

    info(ln('app.verifying_script'));

    if ( ! $name) {
      error(ln("app.missing_script_name"));
    } else {
      $trap = function () {
        include func_get_arg(0);
        return get_defined_vars();
      };


      $script_file = CWD.DS.$name.EXT;

      if (is_file($script_file)) {
        success(ln('app.executing_script', array('path' => str_replace(CWD.DS, '', $script_file))));
        require $script_file;
        bold(ln('tetl.done'));
        exit;
      }

      $script_file  = CWD.DS.'tasks'.DS.$name;
      $script_file .= is_dir($script_file) ? DS.'initialize' : '';
      $script_file .= EXT;

      $path = str_replace(CWD.DS, '', $script_file);

      if ( ! is_file($script_file)) {
        error(ln('app.missing_script_file', array('name' => $path)));
      } else {
        ! $key && $key = 'default';

        $test = $trap($script_file);


        if (empty($test['params'])) {
          error(ln('app.missing_script_params'));
        } elseif ( ! array_key_exists($key, $test['params'])) {
          error(ln('app.unknown_script_param', array('name' => $key)));
        } else {
          success(ln('app.executing_task', array('name' => $path, 'param' => $key)));

          $args = array_slice(func_get_args(), 1);

          call_user_func_array($test['params'][$key], $args);
        }
      }
    }
    bold(ln('tetl.done'));
  }

}

/* EOF: ./stack/console/mods/app/generator.php */
