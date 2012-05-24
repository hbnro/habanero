<?php

/**
 * Taml initialization
 */

/**
 * @ignore
 */

require __DIR__.DS.'tamal'.EXT;

i18n::load_path(__DIR__.DS.'locale', 'tamal');

// allow for tamal files
partial::register('tamal', function ($file, array $vars = array()) {
  return tamal::render($file, $vars);
});

/* EOF: ./library/tamal/initialize.php */
