<?php

/**
 * Generator base
 */

class app_generator extends prototype
{

  /**#@+
   * @ignore
   */

  // usage stack
  private static $help = array();

  // alises stack
  private static $alias = array();

  // tasks collection
  private static $tasks = array();

  /**#@-*/



  /**
   * Configure help
   */
  final public static function usage($namespace, $title, $help) {
    static::$help[$namespace] = array($title, $help);
  }


  /**
   * Configure aliases
   */
  final public static function alias($from, $to) {
    static::$alias[$from] = ! is_array($to) ? explode(' ', $to) : $to;
  }


  /**
   * Retrieve usage text
   */
  final public static function help($all = FALSE) {
    if ( ! empty(static::$help[$all])) {
      @list($title, $text) = static::$help[$all];

      $pad = str_repeat('=', strlen($title) + 2);
      $str = "  $pad\n   $title\n  $pad\n$text";
    } else {
      $str = ln('generator_intro');

      if (is_true($all)) {
        foreach (array_keys(static::$help) as $one) {
          $str .= static::help($one);
        }
      }
    }
    return $str;
  }


  /**
   * Execution!
   */
  final public static function exec($mod, array $vars) {
    foreach (static::$alias as $key => $one) {
      if (in_array($mod, $one)) {
        $mod = $key;
        break;
      }
    }


    if (in_array($mod, get_class_methods(__CLASS__)) OR ! static::defined($mod)) {
      error(ln('undefined_cmd', array('name' => $mod)));
    } else {
      static::apply($mod, $vars);
    }
  }


  /**
   * Check tasks existence
   */
  final public static function exists($namespace, $task = 'default') {
    if ( ! empty(static::$tasks[$namespace])) {
      return ! empty(static::$tasks[$namespace][$task]);
    }
  }


  /**
   * Tasks registry
   */
  final public static function task($namespace, $params = NULL) {
    if (is_file($namespace)) {
      static::$tasks[extn($namespace, TRUE)]['default'] = array('script' => $namespace);
    } else {
      if (strpos($namespace, ':')) {
        @list($namespace, $task) = explode(':', $namespace);

        $test = $params;
        $params = array();
        $params[$task] = $test;
      }

      ! isset(static::$tasks[$namespace]) && static::$tasks[$namespace] = array();

      if (is_closure($params)) {
        static::$tasks[$namespace]['default'] = array('exec' => $params);
      } elseif (empty($params['exec'])) {
        static::$tasks[$namespace] = array_merge(static::$tasks[$namespace], $params);
      } else {
        static::$tasks[$namespace]['default'] = $params;
      }
    }
  }


  /**
   * Execute task
   */
  final public static function run($namespace, $method = 'default') {
    if ( ! empty(static::$tasks[$namespace])) {
      if ( ! empty(static::$tasks[$namespace][$method]['script'])) {
        require static::$tasks[$namespace][$method]['script'];
      } elseif ( ! empty(static::$tasks[$namespace][$method]['exec'])) {
        $config = APP_PATH.DS.'tasks'.DS.$namespace.DS.'config.php';
        call_user_func(static::$tasks[$namespace][$method]['exec'], is_file($config) ? call_user_func(function () {
          require func_get_arg(0);
          return get_defined_vars();
        }, $config) : array());
      } else {
        error(ln('unknown_task_command', array('command' => $method)));
      }
    } else {
      error(ln('missing_task_namespace', array('namespace' => $namespace)));
    }
  }


  /**
   * List tasks
   */
  final public static function all() {
    foreach (static::$tasks as $ns => $set) {
      foreach ($set as $key => $val) {
        $cmd = ($key <> 'default') ? "$ns:$key" : $ns;
        $pad = str_repeat(' ', 40 - strlen($cmd));

        if ( ! empty($val['desc'])) {
          cli::printf("  \bbrown(%s)\b$pad\cdark_gray(#)\c \clight_gray(%s)\c\n", $cmd, $val['desc']);
        } else {
          cli::printf("  \bbrown(%s)\b\n", $cmd);
        }
      }
    }
  }

}

/* EOF: ./stack/app_generator.php */
