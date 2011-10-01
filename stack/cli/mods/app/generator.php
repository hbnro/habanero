<?php

i18n::load_path(__DIR__.DS.'locale', 'app');


class app_generator extends prototype
{
  final public static function st()
  {
    info(ln('app.verifying_installation'));

    if ( ! is_file(CWD.DS.'initialize'.EXT))
    {
      error(ln('app.not_installed'));
    }
    else
    {
      $test  = dir2arr(CWD, '*', DIR_RECURSIVE | DIR_MAP);
      $count = sizeof($test);
      $size  = 0;

      foreach ($test as $file)
      {
        $size += filesize($file);
      }

      success(ln('app.counting_files', array('length' => number_format($count))));
      success(ln('app.sizing_files', array('size' => fmtsize($size))));
      success(ln('app.environment', array('env' => option('environment'))));

      bold(ln('tetl.done'));
    }
  }

  final public static function gen()
  {
    info(ln('app.verifying_installation'));

    if (dirsize(CWD, TRUE))
    {
      notice(ln('app.application'));

      $tmp = dir2arr(CWD, '*', DIR_RECURSIVE | DIR_EMPTY);
      $map = function($tree, $self, $deep = 0)
      {
        foreach ($tree as $key => $val)
        {
          $pre = str_repeat(' ', $deep);

          if (is_array($val))
          {
            cli::writeln("$pre \clight_gray,black($key)\c/");
            $self($val, $self, $deep + 2);
          }
          else
          {
            $size = fmtsize(filesize($val));
            $val  = basename($val);

            cli::writeln("$pre \bwhite($val)\b \clight_gray($size)\c");
          }
        }
      };

      $map($tmp, $map);

      error(ln('app.directory_must_be_empty'));
    }
    else
    {
      success(ln('app.copying_skeleton', array('path' => CWD)));

      $skel_dir = APP_PATH.DS.'core'.DS.'assets'.DS.'skeleton';
      cpfiles($skel_dir, CWD, '*', TRUE);
    }

    bold(ln('tetl.done'));
  }

  final public static function make($what = '', $name = '')
  {
    config(CWD.DS.'config'.DS.'application'.EXT);

    if ( ! in_array($what, array(
      'controller',
      'action',
      'model',
    )))
    {
      static::help();
    }
    else
    {
      info(ln('app.verifying_generator'));

      if ( ! $name)
      {
        error(ln("app.missing_{$what}_name"));
      }
      else
      {
        switch ($what)
        {
          case 'controller';
            $out_file = mkpath(CWD.DS.'app'.DS.'controllers').DS.$name.EXT;

            if (is_file($out_file))
            {
              error(ln('app.controller_already_exists', array('name' => $name)));
            }
            else
            {
              $type = cli::flag('parent') ?: 'controller';
              $code = "<?php\n\nclass {$name}_controller extends $type\n{"
                    . "\n\n  public static function index()\n"
                    . "  {\n  }\n\n}\n";

              success(ln('app.controller_class_building', array('name' => $name)));
              write($out_file, $code);

              success(ln('app.controller_route_building', array('name' => $name)));

              $route_file = CWD.DS.'app'.DS.'routes'.EXT;
              write($route_file, preg_replace('/;[^;]*?$/', ";\nget('/$name', '$name#index', array('path' => '$name'))\\0", read($route_file)));


              if (cli::flag('helper'))
              {
                success(ln('app.controller_helper_building', array('name' => $name)));
                write(mkpath(CWD.DS.'app'.DS.'helpers').DS.$name.EXT, "<?php\n");
              }


              if (cli::flag('view'))
              {
                success(ln('app.controller_view_building', array('name' => $name)));

                $text = "<h1>$name#index.view</h1>\n<p><?php echo __FILE__; ?><br>\n<?php echo ticks(BEGIN), 's'; ?></p>\n";
                write(mkpath(CWD.DS.'app'.DS.'views'.DS.'scripts'.DS.$name).DS.'index'.EXT, $text);
              }
            }
          break;
          case 'action';
            @list($parent, $name) = explode(':', $name);

            $out_file = mkpath(CWD.DS.'app'.DS.'controllers').DS.$parent.EXT;

            if ( ! $parent)
            {
              error(ln('app.controller_missing'));
            }
            elseif ( ! is_file($out_file))
            {
              error(ln('app.controller_not_exists', array('name' => $parent)));
            }
            elseif ( ! $name)
            {
              error(ln("app.missing_{$what}_name"));
            }
            else
            {
              $content = read($out_file);

              if (preg_match("/\b(?:private|public)\s+static\s+function\s+$name\s*\(/s", $content))
              {
                error(ln('app.action_already_exists', array('name' => $name, 'controller' => $parent)));
              }
              else
              {
                success(ln('app.action_method_building', array('name' => $name, 'controller' => $parent)));

                $code = "  public static function $name()\n"
                      . "  {\n  }\n\n";

                write($out_file, preg_replace('/\}[^{}]*?$/s', "$code\\0", $content));


                success(ln('app.action_route_building', array('name' => $name, 'controller' => $parent)));

                $route_file = CWD.DS.'app'.DS.'routes'.EXT;
                write($route_file, preg_replace('/;[^;]*?$/', ";\nget('/$parent/$name', '$parent#$name', array('path' => '{$parent}_$name'))\\0", read($route_file)));


                if (cli::flag('view'))
                {
                  success(ln('app.action_view_building', array('name' => $name, 'controller' => $parent)));

                  $text = "<h1>$parent#$name.view</h1>\n<p><?php echo __FILE__; ?><br>\n<?php echo ticks(BEGIN), 's'; ?></p>\n";
                  write(mkpath(CWD.DS.'app'.DS.'views'.DS.'scripts'.DS.$parent).DS.$name.EXT, $text);
                }
              }
            }
          break;
          case 'model';
            @list($name, $table) = explode(':', $name);

            $out_file = mkpath(CWD.DS.'app'.DS.'models').DS.$name.EXT;

            if (is_file($out_file))
            {
              error(ln('app.model_already_exists', array('name' => $name)));
            }
            else
            {
              success(ln('app.model_class_building', array('name' => $name)));

              $type   = cli::flag('parent') ?: 'dbmodel';
              $parent = $table ? "\n  public static \$table = '$table';" : '';
              $code   = "<?php\n\nclass $name extends $type"
                      . "\n{{$parent}\n}\n";

              write($out_file, $code);
            }
          break;
          default;
          break;
        }
      }
    }

    bold(ln('tetl.done'));
  }

  final public static function conf()
  {
    cli::writeln(pretty(function()
    {
      $trap = function()
      {
        if (is_file(func_get_arg(0)))
        {
          $test = include func_get_arg(0);

          is_array($test) && extract($test);

          unset($test);
        }
        return isset($config) ? $config : get_defined_vars();
      };


      $what = 'current';

      if (cli::flag('dev'))
      {
        $what = 'development';
        $file = CWD.DS.'config'.DS.'environments'.DS.$what.EXT;
      }
      elseif (cli::flag('test'))
      {
        $what = 'testing';
        $file = CWD.DS.'config'.DS.'environments'.DS.$what.EXT;
      }
      elseif (cli::flag('prod'))
      {
        $what = 'production';
        $file = CWD.DS.'config'.DS.'environments'.DS.$what.EXT;
      }
      elseif (cli::flag('app'))
      {
        $what = 'application';
        $file = CWD.DS.'config'.DS.$what.EXT;
      }
      elseif (cli::flag('db'))
      {
        $what = 'database';
        $file = CWD.DS.'config'.DS.$what.EXT;
      }
      elseif (cli::flag('global'))
      {
        $file = CWD.DS.'config'.EXT;
        $what = 'default';
      }

      info(ln("app.{$what}_configuration"));

      $config = isset($file) ? $trap($file) : config();

      $vars = array_slice(cli::args(), 1);
      $vars = array_diff_key($vars, array_flip(array('global', 'prod', 'test', 'dev', 'app', 'db')));

      if ( ! empty($vars))
      {
        success(ln("app.setting_{$what}_options"));
        dump($vars, TRUE);

        $code = '';

        foreach ($vars as $item => $value)
        {
          $sub = explode('.', $item);
          $key = "['" . join("']['", $sub) . "']";

          $value = trim(var_export($value, TRUE));
          $value = is_num($value) ? substr($value, 1, -1) : $value;

          $code .= "\$config{$key} = $value;\n";
        }

        if (isset($file))
        {
          ! is_file($file) && mkpath(dirname($file)) && write($file, "<?php\n\n");
          write($file, $code, 1);
        }
      }
      else
      {
        dump($config, TRUE);
      }
    }));

    bold(ln('tetl.done'));
  }

  final public static function run($name = '')
  {
    @list($name, $key) = explode(':', $name);

    info(ln('app.verifying_script'));

    if ( ! $name)
    {
      error(ln("app.missing_script_name"));
    }
    else
    {
      $trap = function()
      {
        include func_get_arg(0);
        return get_defined_vars();
      };


      $script_file  = CWD.DS.'tasks'.DS.$name;
      $script_file .= is_dir($script_file) ? DS.$name : '';
      $script_file .= EXT;

      if ( ! is_file($script_file))
      {
        error(ln('app.missing_script_file', array('name' => $script_file)));
      }
      else
      {
        ! $key && $key = 'default';

        $test = $trap($script_file);


        if (empty($test['params']))
        {
          error(ln('app.missing_script_params'));
        }
        elseif ( ! array_key_exists($key, $test['params']))
        {
          error(ln('app.unknown_script_param', array('name' => $key)));
        }
        else
        {
          success(ln('app.executing_script', array('name' => $script_file)));

          $args = array_slice(func_get_args(), 1);

          apply($test['params'][$key], $args);
        }
      }
    }
    bold(ln('tetl.done'));
  }

}

/* EOF: ./cli/mods/app/generator.php */
