<?php

namespace Sauce\Shell;

class Task
{

  private static $help = array();
  private static $alias = array();
  private static $tasks = array();

  private static $no_help = 'Not available';
  private static $welcome = <<<INTRO

  Welcome to the \blight_gray,black(habanero-sauce)\b console utility!

  Usage:
    hs \bgreen(<command>)\b [arguments] [...]

  Extras:
    --config \bcyan([--item=value])\b   Display and set the configuration options
             \clight_gray([...] [--global|app|dev|prod])\c
    --assets \bcyan(action)\b           Clean and precompile application assets
    --help                    Display the descriptions of all tasks
INTRO;



  public static function usage($namespace, $help)
  {
    static::$help[$namespace] = $help;
  }

  public static function alias($from, $to)
  {
    static::$alias[$from] = ! is_array($to) ? explode(' ', (string) $to) : $to;
  }

  public static function help($all = FALSE)
  {
    if ($all && ! sizeof(static::$help))
    {
      return static::$no_help;
    }


    if ( ! empty(static::$help[$all])) {
      $text = static::$help[$all];
      $str  = "  $text\n\n";
    } else {
      $str = '';

      if ($all) {
        foreach (array_keys(static::$help) as $one) {
          $str .= rtrim(static::help($one));
          $str .= "\n";
        }
        $str .= "\n\n";
      } else {
        $str .= static::$welcome;
        $str .= "\n";
      }
    }
    return $str;
  }

  public static function exec($mod, array $vars)
  {
    foreach (static::$alias as $key => $one) {
      if (in_array($mod, $one)) {
        $mod = $key;
        break;
      }
    }


    @list($namespace, $task) = explode(':', $mod);

    if ( ! static::exists($namespace, $task ?: 'default')) {
      $suffix = is_string($mod) ? ": $mod" : '';
      throw new \Exception("Undefined option$suffix");
    } else {
      static::run($namespace, $task);
    }
  }

  public static function exists($namespace, $task = 'default')
  {
    if ( ! empty(static::$tasks[$namespace])) {
      return ! empty(static::$tasks[$namespace][$task]);
    }
  }

  public static function task($namespace, $params = NULL)
  {
    if (is_file($namespace)) {
      static::$tasks[basename($namespace, '.php')]['default'] = array('script' => $namespace);
    } else {
      if (strpos($namespace, ':')) {
        @list($namespace, $task) = explode(':', $namespace);

        $test = $params;
        $params = array();
        $params[$task] = $test;
      }

      ! isset(static::$tasks[$namespace]) && static::$tasks[$namespace] = array();

      if ($params instanceof \Closure) {
        static::$tasks[$namespace]['default'] = array('exec' => $params);
      } elseif (empty($params['exec'])) {
        static::$tasks[$namespace] = array_merge(static::$tasks[$namespace], $params);
      } else {
        static::$tasks[$namespace]['default'] = $params;
      }
    }
  }

  public static function run($namespace, $method = 'default')
  {
    if ( ! empty(static::$tasks[$namespace])) {
      if ( ! empty(static::$tasks[$namespace][$method]['script'])) {
        require static::$tasks[$namespace][$method]['script'];
      } elseif ( ! empty(static::$tasks[$namespace][$method]['exec'])) {
        $config = path(getcwd(), 'tasks', $namespace, 'config.php');
        $config = is_file($config) ? call_user_func(function () {
            require func_get_arg(0);
            return get_defined_vars();
          }, $config) : array();

        call_user_func(static::$tasks[$namespace][$method]['exec'], $config);
      } else {
        throw new \Exception("Unknown '$method' command");
      }
    } else {
      throw new \Exception("Missing '$namespace' namespace");
    }
  }

  public static function all()
  {
    foreach (static::$tasks as $ns => $set) {
      foreach ($set as $key => $val) {
        $cmd = ($key <> 'default') ? "$ns:$key" : $ns;
        $pad = str_repeat(' ', 40 - strlen($cmd));

        if ( ! empty($val['desc'])) {
          static::printf("  \bbrown(%s)\b$pad\cdark_gray(#)\c \clight_gray(%s)\c\n", $cmd, $val['desc']);
        } else {
          static::printf("  \bbrown(%s)\b\n", $cmd);
        }
      }
    }
  }

}
