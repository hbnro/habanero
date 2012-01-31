<?php

/**
 * Startup class!
 */

class App {
  /**#@+
   * @ignore
   */

  // preload list
  private static $lib = array();

  // import dependencies
  final public static function load($name) {
    ! in_array($name, static::$lib) && static::$lib []= $name;
  }

  // finally execute the app
  final public static function run(Closure $lambda) {
    foreach(static::$lib as $one) {
      import($one);
    }
    run($lambda);
  }

  /**#@-*/
}

/**
 * @ignore
 */
require join(DIRECTORY_SEPARATOR, array(dirname(__DIR__), 'framework', 'initialize.php'));

/* EOF: ./framework/package/App.php */
