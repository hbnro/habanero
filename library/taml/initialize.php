<?php

/**
 * Taml initialization
 */

/**
 * @ignore
 */

require __DIR__.DS.'taml'.EXT;

i18n::load_path(__DIR__.DS.'locale', 'taml');

if (class_exists('partial', FALSE)) {
  // allow for taml files
  partial::register('taml', function ($file, array $vars = array()) {
    return taml::render($file, $vars);
  });
}

/* EOF: ./library/taml/initialize.php */
