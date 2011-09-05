<?php

class application extends prototype
{
  final public static function help()
  {
    $app_introduction = ln('tetl.application_generator');
    $str = <<<HELP

  $app_introduction

  \bgreen(app:st)\b
  \bgreen(app:gen)\b
  \bgreen(app:make)\b \bblue(controller)\b \byellow(name)\b
  \bgreen(app:make)\b \bblue(action)\b \byellow(name)\b \bwhite(controller)\b
  \bgreen(app:make)\b \bblue(model)\b \byellow(name)\b [table]

HELP;

    cli::write(cli::format("$str\n"));
  }

  final public static function st()
  {
    info(ln('tetl.verifying_installation'));

    if ( ! is_file(CWD.DS.'app'.DS.'initialize'.EXT))
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
    $config_file = CWD.DS.'config'.DS.'application'.EXT;

    is_file($config_file) && config($config_file);


    @list($what, $name, $parent) = $args;

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
            $out_file = option('mvc.controllers_path').DS.$name.EXT;

            if (is_file($out_file))
            {
              error(ln('tetl.controller_already_exists', array('name' => $name)));
            }
            else
            {
              $controller_tpl = "<?php\n\nclass {$name}_controller extends controller\n{"
                              . "\n  public static function index()\n"
                              . "  {\n  }\n}\n";

              success(ln('tetl.controller_class_building', array('name' => $name)));
              write($out_file, $controller_tpl);

              success(ln('tetl.controller_route_building', array('name' => $name)));

              $route_file = CWD.DS.'app'.DS.'routes'.EXT;
              write($route_file, preg_replace('/;[^;]*?$/', ";\nroute('/$name', '$name#index', array('path' => '$name'))\\0", read($route_file)));


              success(ln('tetl.controller_view_building', array('name' => $name)));

              $ext = ! empty($params['type']) ? '.' . $params['type'] : EXT;

              $text = "<h1>$name#index.view</h1>\n<p><?php echo __FILE__; ?></p>\n<?php echo ticks(BEGIN), 's';\n";
              write(mkpath(option('mvc.views_path').DS.'scripts'.DS.$name).DS.'index'.$ext, $text);
            }
          break;
          case 'action';
            $out_file = option('mvc.controllers_path').DS.$parent.EXT;

            if ( ! $parent)
            {
              error(ln('tetl.controller_missing'));
            }
            elseif ( ! is_file($out_file))
            {
              error(ln('tetl.controller_not_exists', array('name' => $parent)));
            }
            else
            {
              if (preg_match("/\b(?:private|public)\s+static\s+function\s+$name\s*\(/s", read($out_file)))
              {
                error(ln('tetl.action_already_exists', array('name' => $name, 'controller' => $parent)));
              }
              else
              {
                success(ln('tetl.action_method_building', array('name' => $name, 'controller' => $parent)));

                $action_tpl = "  public static function $name()\n"
                            . "  {\n  }\n";

                write($out_file, preg_replace('/\}[^{}]*?$/s', "$action_tpl\\0", read($out_file)));


                success(ln('tetl.action_route_building', array('name' => $name, 'controller' => $parent)));

                $route_file = CWD.DS.'app'.DS.'routes'.EXT;
                write($route_file, preg_replace('/;[^;]*?$/', ";\nroute('/$parent/$name', '$parent#$name', array('path' => '{$parent}_$name'))\\0", read($route_file)));


                success(ln('tetl.action_view_building', array('name' => $name, 'controller' => $parent)));

                $text = "<h1>$parent#$name.view</h1>\n<p><?php echo __FILE__; ?></p>\n<?php echo ticks(BEGIN), 's';\n";
                write(mkpath(CWD.DS.'app'.DS.'views'.DS.'scripts'.DS.$parent).DS.$name.EXT, $text);
              }
            }
          break;
          case 'model';
            $out_file = option('mvc.models_path').DS.$name.EXT;

            if (is_file($out_file))
            {
              error(ln('tetl.model_already_exists', array('name' => $name)));
            }
            else
            {
              success(ln('tetl.model_class_building', array('name' => $name)));

              $parent = $parent ? "\n  public static \$table = '$parent';" : '';
              $code   = "<?php\n\nclass $name extends model"
                      . "\n{{$parent}\n}\n";

              write($out_file, $code);
            }
          break;
          default;
            error(ln('tetl.unknown_generator', array('name' => $what)));
          break;
        }
      }
    }

    bold(ln('tetl.done'));
  }
}
