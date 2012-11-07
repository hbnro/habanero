<?php

namespace Sauce\Shell;

class Task
{

  private static $tasks = array();

  private static $welcome = <<<INTRO

  Welcome to the \blight_gray,black(habanero-sauce)\b console utility!

  Usage:
    @ \bgreen(<command>)\b [arguments] [...]

  Type \bwhite(--help)\b to get more information

INTRO;



  public static function help($all = FALSE)
  {
    $cmd = ! empty($_SERVER['_']) ? basename($_SERVER['_']) : 'hs';

    \Sauce\Shell\CLI::printf(str_replace('@', $cmd, static::$welcome) . "\n");

    if ($all) {
      if ( ! sizeof(static::$tasks)) {
        \Sauce\Shell\CLI::printf("  \bred(Not available tasks!)\b\n\n");
      } else {
        $max = 0;

        foreach (static::$tasks as $ns => $set) {
          foreach (array_keys($set) as $k) {
            $cmd = ($k <> 'default') ? "$ns:$k" : $ns;
            $max = ($test = strlen($cmd)) > $max ? $test : $max;
          }
        }


        \Sauce\Shell\CLI::printf("  \bcyan(Available tasks:)\b\n\n");

        foreach (static::$tasks as $ns => $set) {
          foreach ($set as $key => $val) {
            $cmd = ($key <> 'default') ? "$ns:$key" : $ns;
            $pad = str_repeat(' ', ($max + 2) - strlen($cmd));

            if ( ! empty($val['desc'])) {
              \Sauce\Shell\CLI::printf("  \bbrown(%s)\b$pad\cdark_gray(#)\c \clight_gray(%s)\c\n", $cmd, $val['desc']);
            } else {
              \Sauce\Shell\CLI::printf("  \bbrown(%s)\b\n", $cmd);
            }
          }
        }

        \Sauce\Shell\CLI::writeln();
      }
    }
  }

  public static function exec($mod, array $vars)
  {
    @list($namespace, $task) = explode(':', $mod, 2);

    if ( ! static::exists($namespace, $task ?: 'default')) {
      $suffix = is_string($mod) ? ": $mod" : '';
      throw new \Exception("Undefined option$suffix");
    } else {
      static::run($namespace, $task ?: 'default', $vars);
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
        require static::$tasks[$namespace][$method]['script'];
      } elseif ( ! empty(static::$tasks[$namespace][$method]['exec'])) {
        call_user_func(static::$tasks[$namespace][$method]['exec'], $params);
      } else {
        throw new \Exception("Unknown '$method' command");
      }
    } else {
      throw new \Exception("Missing '$namespace' namespace");
    }
  }

}
