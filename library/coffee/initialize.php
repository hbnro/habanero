<?php

/**
 * Coffee wrapper initialization
 */

if (class_exists('partial', FALSE)) {
  /**
   * @ignore
   */
  require __DIR__.DS.'vendor'.DS.'coffeescript'.EXT;

  // TODO: there is another solution?
  partial::register('coffee', function ($file, array $vars = array()) {
    return Coffeescript\compile(read($file), array(
      'bare' => TRUE,
    ));
  });
}

if (class_exists('assets', FALSE)) {
  // assets compiler
  assets::compile('coffee', function ($file) {
    return partial::render($file);
  });
}


/**
 * Wrapper class
 */
class coffee
{
  // file render
  final public static function compile($file) {
    return Coffeescript\compile(read($file));
  }
  // text parse
  final public static function parse($text) {
    return Coffeescript\compile($text);
  }
}

/* EOF: ./library/coffee/initialize.php */
