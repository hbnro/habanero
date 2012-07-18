<?php

/**
 * Coffee wrapper initialization
 */

$old = error_reporting();
error_reporting(0);

/**
 * @ignore
 */
require __DIR__.DS.'vendor'.DS.'coffeescript'.EXT;

error_reporting($old);


partial::register('coffee', function ($context) {
  return coffee::parse($context);
});


/**
 * Wrapper class
 */
class coffee
{
  // text parse
  final public static function parse($text) {
    return Coffeescript\compile($text, array(
      'bare' => TRUE,
    ));
  }
}

/* EOF: ./library/coffee/initialize.php */
