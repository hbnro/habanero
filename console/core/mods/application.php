<?php

class application extends prototype
{
  final public static function help()
  {
    $app_introduction = ln('tetl.application_generator');
    $str = <<<HELP

  $app_introduction

  \bgreen(app.st)\b
  \bgreen(app.gen)\b
  \bgreen(app.make)\b \bcyan(controller)\b \byellow(name)\b [--view] [--helper] [--parent=class]
  \bgreen(app.make)\b \bcyan(action)\b \byellow(controller:name)\b [--view]
  \bgreen(app.make)\b \bcyan(model)\b \byellow(name[:table])\b [--parent=class]
  \bgreen(app.conf)\b \bcyan(--option=value)\b [...]
  \bgreen(app.run)\b \bcyan(script[:param])\b [...]

HELP;

    cli::write(cli::format("$str\n"));
  }

  final public static function st()
  {
    info(ln('tetl.verifying_installation'));

    if ( ! is_file(CWD.DS.'initialize'.EXT))
    {
      error(ln('tetl.not_installed'));
    }
    else
    {
      $test = dir2arr(CWD, '*', DIR_RECURSIVE | DIR_MAP);
      $count = sizeof($test);
      $size = 0;

      foreach ($test as $file)
      {
        $size += filesize($file);
      }

      success(ln('tetl.counting_files', array('length' => number_format($count))));
      success(ln('tetl.sizing_files', array('size' => fmtsize($size))));
      success(ln('tetl.environment', array('env' => option('environment'))));

      bold(ln('tetl.done'));
    }
  }

  final public static function gen()
  {
    info(ln('tetl.verifying_installation'));

    if (dirsize(CWD, TRUE))
    {
      notice(ln('tetl.application'));

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

      error(ln('tetl.directory_must_be_empty'));
    }
    else
    {
      success(ln('tetl.copying_skeleton', array('path' => CWD)));

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
      self::help();
    }
    else
    {
      info(ln('tetl.verifying_generator'));

      if ( ! $name)
      {
        error(ln("tetl.missing_{$what}_name"));
      }
      else
      {
        switch ($what)
        {
          case 'controller';
            $out_file = mkpath(option('mvc.controllers_path')).DS.$name.EXT;

            if (is_file($out_file))
            {
              error(ln('tetl.controller_already_exists', array('name' => $name)));
            }
            else
            {
              $type = cli::flag('parent') ?: 'controller';
              $code = "<?php\n\nclass {$name}_controller extends $type\n{"
                    . "\n\n  public static function index()\n"
                    . "  {\n  }\n\n}\n";

              success(ln('tetl.controller_class_building', array('name' => $name)));
              write($out_file, $code);

              success(ln('tetl.controller_route_building', array('name' => $name)));

              $route_file = CWD.DS.'app'.DS.'routes'.EXT;
              write($route_file, preg_replace('/;[^;]*?$/', ";\nget('/$name', '$name#index', array('path' => '$name'))\\0", read($route_file)));


              if (cli::flag('helper'))
              {
                success(ln('tetl.controller_helper_building', array('name' => $name)));
                write(mkpath(option('mvc.helpers_path')).DS.$name.EXT, "<?php\n");
              }


              if (cli::flag('view'))
              {
                success(ln('tetl.controller_view_building', array('name' => $name)));

                $text = "<h1>$name#index.view</h1>\n<p><?php echo __FILE__; ?><br>\n<?php echo ticks(BEGIN), 's'; ?></p>\n";
                write(mkpath(option('mvc.views_path').DS.'scripts'.DS.$name).DS.'index'.EXT, $text);
              }
            }
          break;
          case 'action';
            @list($parent, $name) = explode(':', $name);

            $out_file = mkpath(option('mvc.controllers_path')).DS.$parent.EXT;

            if ( ! $parent)
            {
              error(ln('tetl.controller_missing'));
            }
            elseif ( ! is_file($out_file))
            {
              error(ln('tetl.controller_not_exists', array('name' => $parent)));
            }
            elseif ( ! $name)
            {
              error(ln("tetl.missing_{$what}_name"));
            }
            else
            {
              $content = read($out_file);

              if (preg_match("/\b(?:private|public)\s+static\s+function\s+$name\s*\(/s", $content))
              {
                error(ln('tetl.action_already_exists', array('name' => $name, 'controller' => $parent)));
              }
              else
              {
                success(ln('tetl.action_method_building', array('name' => $name, 'controller' => $parent)));

                $code = "  public static function $name()\n"
                      . "  {\n  }\n\n";

                write($out_file, preg_replace('/\}[^{}]*?$/s', "$code\\0", $content));


                success(ln('tetl.action_route_building', array('name' => $name, 'controller' => $parent)));

                $route_file = CWD.DS.'app'.DS.'routes'.EXT;
                write($route_file, preg_replace('/;[^;]*?$/', ";\nget('/$parent/$name', '$parent#$name', array('path' => '{$parent}_$name'))\\0", read($route_file)));


                if (cli::flag('view'))
                {
                  success(ln('tetl.action_view_building', array('name' => $name, 'controller' => $parent)));

                  $text = "<h1>$parent#$name.view</h1>\n<p><?php echo __FILE__; ?><br>\n<?php echo ticks(BEGIN), 's'; ?></p>\n";
                  write(mkpath(option('mvc.views_path').DS.'scripts'.DS.$parent).DS.$name.EXT, $text);
                }
              }
            }
          break;
          case 'model';
            @list($name, $table) = explode(':', $name);

            $out_file = mkpath(option('mvc.models_path')).DS.$name.EXT;

            if (is_file($out_file))
            {
              error(ln('tetl.model_already_exists', array('name' => $name)));
            }
            else
            {
              success(ln('tetl.model_class_building', array('name' => $name)));

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
    info(ln('tetl.configuration'));

    cli::writeln(pretty(function()
    {
      dump(config(), TRUE);
    }));

    bold(ln('tetl.done'));
  }

  final public static function run($name = '')
  {
    @list($name, $key) = explode('#', $name);

    info(ln('tetl.verifying_script'));

    if ( ! $name)
    {
      error(ln("tetl.missing_script_name"));
    }
    else
    {
      $trap = function()
      {
        include func_get_arg(0);
        return get_defined_vars();
      };


      $script_file  = CWD.DS.$name;
      $script_file .= is_dir($script_file) ? DS.$name : '';
      $script_file .= EXT;

      if ( ! is_file($script_file))
      {
        error(ln('tetl.missing_script_file', array('name' => $script_file)));
      }
      else
      {
        ! $key && $key = 'default';

        $test = $trap($script_file);


        if (empty($test['params']))
        {
          error(ln('tetl.missing_script_params'));
        }
        elseif ( ! array_key_exists($key, $test['params']))
        {
          error(ln('tetl.unknown_script_param', array('name' => $key)));
        }
        else
        {
          success(ln('tetl.executing_script', array('name' => $script_file)));

          $args = array_slice(func_get_args(), 1);

          apply($test['params'][$key], $args);
        }
      }
    }
    bold(ln('tetl.done'));
  }

}
