<?php

class application extends prototype
{
  final public static function help()
  {
    $app_introduction = ln('tetl.application_generator');
    $str = <<<HELP

  $app_introduction

  \cgreen(app.st)\c
  \cgreen(app.gen)\c
  \cgreen(app.make)\c \cpurple(controller)\c \cyellow(name)\c
  \cgreen(app.make)\c \cpurple(action)\c \cyellow(controller:name)\c
  \cgreen(app.make)\c \cpurple(model)\c \cyellow(name[:table])\c
  \cgreen(app.run)\c \cpurple(script[:param])\c [...]

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
    }

    bold(ln('tetl.done'));
  }

  final public static function gen($args = array())
  {
    info(ln('tetl.verifying_installation'));

    @list($name) = $args;

    if ( ! $name)
    {
      $name = basename(CWD);
    }

    if (dirsize(CWD, TRUE))
    {
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

  final public static function make($args = array(), $params = array())
  {
    config(CWD.DS.'config'.DS.'application'.EXT);

    @list($what, $name) = $args;

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
              $controller_tpl = "<?php\n\nclass {$name}_controller extends controller\n{"
                              . "\n\n  public static function index()\n"
                              . "  {\n  }\n\n}\n";

              success(ln('tetl.controller_class_building', array('name' => $name)));
              write($out_file, $controller_tpl);

              success(ln('tetl.controller_route_building', array('name' => $name)));

              $route_file = CWD.DS.'app'.DS.'routes'.EXT;
              write($route_file, preg_replace('/;[^;]*?$/', ";\nget('/$name', '$name#index', array('path' => '$name'))\\0", read($route_file)));


              success(ln('tetl.controller_view_building', array('name' => $name)));

              $ext = ! empty($params['type']) ? '.' . $params['type'] : EXT;

              $text = "<h1>$name#index.view</h1>\n<p><?php echo __FILE__; ?><br>\n<?php echo ticks(BEGIN), 's'; ?></p>\n";
              write(mkpath(option('mvc.views_path').DS.'scripts'.DS.$name).DS.'index'.$ext, $text);
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

                $action_tpl = "  public static function $name()\n"
                            . "  {\n  }\n\n";

                write($out_file, preg_replace('/\}[^{}]*?$/s', "$action_tpl\\0", $content));


                success(ln('tetl.action_route_building', array('name' => $name, 'controller' => $parent)));

                $route_file = CWD.DS.'app'.DS.'routes'.EXT;
                write($route_file, preg_replace('/;[^;]*?$/', ";\nget('/$parent/$name', '$parent#$name', array('path' => '{$parent}_$name'))\\0", read($route_file)));


                success(ln('tetl.action_view_building', array('name' => $name, 'controller' => $parent)));

                $text = "<h1>$parent#$name.view</h1>\n<p><?php echo __FILE__; ?><br>\n<?php echo ticks(BEGIN), 's'; ?></p>\n";
                write(mkpath(option('mvc.views_path').DS.'scripts'.DS.$parent).DS.$name.EXT, $text);
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

              $parent = $table ? "\n  public static \$table = '$table';" : '';
              $code   = "<?php\n\nclass $name extends model"
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

  final public static function run($args = array())
  {
    $name = array_shift($args);
    $key = array_shift($args);

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

          apply($test['params'][$key], $args);
        }
      }
    }
    bold(ln('tetl.done'));
  }

}
