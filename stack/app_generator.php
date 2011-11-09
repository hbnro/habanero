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
  final public static function usage() {
    static::$help []= func_get_args();
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
    $str = ln('generator_intro');

    if (is_true($all)) {
      foreach (static::$help as $i => $one) {
        @list($title, $text) = $one;

        $pad = str_repeat('=', strlen($title) + 2);
        $str .= "  $pad\n   $title\n  $pad\n$text";
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

}

/* EOF: ./stack/app_generator.php */
