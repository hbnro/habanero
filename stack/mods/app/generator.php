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
      $map = function ($tree, $self, $depth = 0) {
        foreach ($tree as $key => $val) {
          $pre = str_repeat(' ', $depth);

          if (is_array($val)) {
            cli::writeln("$pre  \clight_gray,black($key/)\c");
            $self($val, $self, $depth + 2);
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
      if (is_file($script_file = CWD.DS.$name.EXT)) {
        success(ln('app.executing_script', array('path' => str_replace(CWD.DS, '', $script_file))));
        require $script_file;
        bold(ln('tetl.done'));
        exit;
      }

      $task_file = CWD.DS.'tasks'.DS.$name.DS.'initialize'.EXT;
      $path      = str_replace(CWD.DS, '', $task_file);

      if ( ! is_file($task_file)) {
        error(ln('app.missing_script_file', array('name' => $path)));
      } else {
        $task_class = "{$name}_task";

        /**#@+
         * @ignore
         */
        require __DIR__.DS.'task_manager'.EXT;
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
        }
      }
    }
    bold(ln('tetl.done'));
  }

}

/* EOF: ./stack/console/mods/app/generator.php */
