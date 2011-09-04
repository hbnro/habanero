<?php

class application extends prototype
{
  public static function help()
  {
    $app_introduction = ln('tetl.app_generator');
    $str = <<<HELP

  $app_introduction

  \bgreen(app:st)\b
  \bgreen(app:gen)\b
  \bgreen(app:make)\b \bblue(controller)\b \bwhite(name)\b
  \bgreen(app:make)\b \bblue(action)\b \bwhite(name)\b \byellow(controller)\b

HELP;

    cli::write(cli::format("$str\n"));
  }

  public static function st()
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

      notice(ln('tetl.counting_files', array('length' => $count)));
      notice(ln('tetl.sizing_files', array('size' => $size)));
    }

    bold(ln('tetl.done'));
  }

  public static function gen($args = array())
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
      info(ln('tetl.copying_skeleton', array('path' => CWD)));

      $skel_dir = __DIR__.DS.'assets'.DS.'skeleton';

      cpfiles($skel_dir, CWD, '*', TRUE);
    }

    bold(ln('tetl.done'));
  }

  public static function make($args = array(), $params = array())
  {
    $config_file = CWD.DS.'config'.DS.'application'.EXT;

    is_file($config_file) && config($config_file);
    

    @list($what, $name, $parent) = $args;

    if ( ! in_array($what, array(
      'controller',
      'action',
    )))
    {
      application::help();
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
            dump(config(), TRUE);
echo $out_file;
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


                success(ln('tetl.action_route_building', array('name' => $name, 'module' => $parent)));

                $route_file = CWD.DS.'app'.DS.'routes'.EXT;
                write($route_file, preg_replace('/;[^;]*?$/', ";\nroute('/$parent/$name', '$parent#$name')\\0", read($route_file)));


                success(ln('tetl.action_view_building', array('name' => $name, 'module' => $parent)));

                $text = "<h1>$parent#$name.view</h1>\n<p><?php echo __FILE__; ?></p>\n<?php echo ticks(BEGIN), 's';\n";
                write(mkpath(CWD.DS.'app'.DS.'views'.DS.'scripts'.DS.$parent).DS.$name.EXT, $text);

                write(option('mvc.helpers_path').DS.$parent.EXT, "<?php\n");
              }
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
