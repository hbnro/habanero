<?php

/**
 * Taml initialization
 */

/**#
 * @ignore
 */
require __DIR__.DS.'tamal'.EXT;

class tamal_helper extends prototype
{// fake class
}

i18n::load_path(__DIR__.DS.'locale', 'tamal');

// allow for tamal files
partial::register('tamal', function ($file, array $vars = array()) {
  return tamal::render($file, $vars);
});

// common helpers
require __DIR__.DS.'helpers'.DS.'php'.EXT;
require __DIR__.DS.'helpers'.DS.'style'.EXT;
require __DIR__.DS.'helpers'.DS.'script'.EXT;
require __DIR__.DS.'helpers'.DS.'escape'.EXT;
require __DIR__.DS.'helpers'.DS.'plain'.EXT;
/**#@-*/

/* EOF: ./library/tamal/initialize.php */
