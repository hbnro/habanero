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

  /**#@-*/



  /**
   * Configure help
   */
  final public static function usage($text) {
    static::$help []= $text;
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
  final public static function help() {
    return join('', static::$help);
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


    if ( ! static::defined($mod)) {
      error(ln('undefined_cmd', array('name' => $mod)));
    } else {
      static::apply($mod, $vars);
    }
  }

}

/* EOF: ./stack/console/app_generator.php */
