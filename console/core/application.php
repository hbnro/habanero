<?php

class application extends prototype
{
  function help()
  {
    $app_introduction = ln('tetl.app_generator_intro');
    $app_title = ln('tetl.app_generator');
    $str = <<<HELP

  $app_introduction

  $app_title:

  \bgreen(app:status)\b
  \bgreen(app:generate)\b \bblue(name)\b
  \bgreen(app:create)\b \bblue(controller)\b \bwhite(name)\b
  \bgreen(app:create)\b \bblue(action)\b \bwhite(name)\b \byellow(parent)\b

HELP;

    cli::write(cli::format("$str\n"));
  }

  function status()
  {
    blue(ln('tetl.verifying_installation'));

    if ( ! is_file(CWD.DS.'app'.DS.'initialize'.EXT))
    {
      red(ln('tetl.not_installed'));
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

      cli::writeln(ln('tetl.counting_files', array('length' => "\bwhite($count)\b")));
      cli::writeln(ln('tetl.sizing_files', array('size' => "\bwhite($size)\b")));
    }

    white(ln('tetl.done'));
  }

  function generate($args = array())
  {
    blue(ln('tetl.verifying_installation'));

    @list($name) = $args;

    if ( ! $name)
    {
      $name = basename(CWD);
    }

    if (dirsize(CWD, TRUE))
    {
      red(ln('tetl.directory_must_be_empty'));
    }
    else
    {
      blue(ln('tetl.copying_skeleton', array('path' => CWD)));

      $skel_dir = __DIR__.DS.'assets'.DS.'skeleton';

      cpfiles($skel_dir, CWD, '*', TRUE);
    }

    white(ln('tetl.done'));
  }

  function create($args = array())
  {
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
      blue(ln('tetl.verifying_generator'));

      if ( ! $name)
      {
        red(ln("tetl.missing_{$what}_name"));
      }
      else
      {
        switch ($what)
        {
          case 'controller';
            $out_file = CWD.DS.'app'.DS.'controller'.DS.$name.EXT;

            if (is_file($out_file))
            {
              red(ln('tetl.controller_already_exists', array('name' => $name)));
            }
            else
            {
              $controller_file = __DIR__.DS.'assets'.DS.'generate'.DS.'app_controller'.EXT;

              green(ln('tetl.controller_class_building', array('name' => $name)));
              write($out_file, sprintf("<?php\n%s", strtr(read($controller_file), array(
                '$name' => $name,
              ))));


              green(ln('tetl.controller_route_building', array('name' => $name)));

              $route_file = CWD.DS.'app'.DS.'routes'.EXT;
              write($route_file, preg_replace('/;[^;]*?$/', ";\nroute('/$name', '$name#index')\\0", read($route_file)));


              green(ln('tetl.controller_view_building', array('name' => $name)));

              $text = "<h1>$name#index.view</h1>\n<p><?php echo __FILE__; ?></p>\n";
              write(mkpath(CWD.DS.'app'.DS.'views'.DS.'scripts'.DS.$name).DS.'index'.EXT, $text);
            }
          break;
          case 'action';
            $out_file = CWD.DS.'app'.DS.'controller'.DS.$parent.EXT;

            if ( ! $parent)
            {
              red(ln('tetl.controller_missing'));
            }
            elseif ( ! is_file($out_file))
            {
              red(ln('tetl.controller_not_exists', array('name' => $parent)));
            }
            else
            {
              if (preg_match("/function\s+$name\s*\(/s", read($out_file)))
              {
                red(ln('tetl.action_already_exists', array('name' => $name, 'module' => $parent)));
              }
              else
              {
                green(ln('tetl.action_method_building', array('name' => $name, 'module' => $parent)));

                $action_file = __DIR__.DS.'assets'.DS.'generate'.DS.'app_controller_action'.EXT;
                write($out_file, preg_replace_callback('/\}[^{}]*?$/s', function($match)
                  use($action_file, $name)
                {
                  return strtr(read($action_file), array(
                    '$action' => $name,
                  )) . $match[0];
                }, read($out_file)));


                green(ln('tetl.action_route_building', array('name' => $name, 'module' => $parent)));

                $route_file = CWD.DS.'app'.DS.'routes'.EXT;
                write($route_file, preg_replace('/;[^;]*?$/', ";\nroute('/$parent/$name', '$parent#$name')\\0", read($route_file)));


                green(ln('tetl.action_view_building', array('name' => $name, 'module' => $parent)));

                $text = "<h1>$parent#$name.view</h1>\n<p><?php echo __FILE__; ?></p>\n";
                write(mkpath(CWD.DS.'app'.DS.'views'.DS.'scripts'.DS.$parent).DS.$name.EXT, $text);
              }
            }
          break;
          default;
            red(ln('tetl.unknown_generator', array('name' => $what)));
          break;
        }
      }
    }

    white(ln('tetl.done'));
  }
}
