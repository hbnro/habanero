<?php

namespace Sauce\Shell;

class Task
{

  private static $tasks = array();

  private static $welcome = <<<INTRO

  Welcome to the \blight_gray,black(habanero-sauce)\b console utility!

  Type \clight_gray(--help)\c to get more information

INTRO;



  public static function help($all = FALSE)
  {
    if ($all) {
      if ( ! sizeof(static::$tasks)) {
        \Sauce\Shell\CLI::error("\n  \cred,black(Not available tasks!)\c\n");
      } else {
        static::search();
      }
    } else {
      \Sauce\Shell\CLI::printf(static::$welcome . "\n");
    }
  }

  public static function exec($mod, array $vars)
  {
    @list($namespace, $task) = explode(':', $mod, 2);

    if ( ! static::exists($namespace, $task ?: 'default')) {
      foreach (array_keys(static::$tasks) as $ns) {
        if (strpos($ns, $mod) === 0) {
          return static::search($mod);
        }
      }

      $argv = join(' ', array_slice($_SERVER['argv'], 1));
      throw new \Exception("Unknown option: $argv");
    } else {
      return static::run($namespace, $task ?: 'default', $vars);
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
        @list($namespace, $task) = explode(':', $namespace, 2);

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

  public static function run($namespace, $method = 'default', array $params = array())
  {
    if ( ! empty(static::$tasks[$namespace])) {
      if ( ! empty(static::$tasks[$namespace][$method]['script'])) {
        return require static::$tasks[$namespace][$method]['script'];
      } elseif ( ! empty(static::$tasks[$namespace][$method]['exec'])) {
        return call_user_func(static::$tasks[$namespace][$method]['exec'], $params);
      } else {
        throw new \Exception("Unknown '$method' command");
      }
    } else {
      throw new \Exception("Missing '$namespace' namespace");
    }
  }


  private static function search($q = '')
  {
    \Sauce\Shell\CLI::printf("\n  \ccyan(Available tasks:)\c\n\n");

    $max = 0;

    foreach (static::$tasks as $ns => $set) {
      foreach (array_keys($set) as $k) {
        $cmd = ($k <> 'default') ? "$ns:$k" : $ns;
        $max = ($test = strlen($cmd)) > $max ? $test : $max;
      }
    }


    foreach (static::$tasks as $ns => $set) {
      if ( ! $q OR (strpos($ns, $q) === 0)) {
        foreach ($set as $key => $val) {
          $cmd = ($key <> 'default') ? "$ns:$key" : $ns;
          $pad = str_repeat(' ', ($max + 2) - strlen($cmd));

          if ( ! empty($val['desc'])) {
            \Sauce\Shell\CLI::printf("  \cbrown(%s)\c$pad\cdark_gray(#)\c \clight_gray(%s)\c\n", $cmd, $val['desc']);
          } else {
            \Sauce\Shell\CLI::printf("  \cbrown(%s)\c\n", $cmd);
          }
        }
      }
    }

    \Sauce\Shell\CLI::writeln();
  }

}
