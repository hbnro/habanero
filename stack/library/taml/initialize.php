<?php

/**
 * Taml initialization
 */

/**
 * @ignore
 */

require __DIR__.DS.'taml_class'.EXT;

i18n::load_path(__DIR__.DS.'locale', 'taml');


// render callback
if (class_exists('partial')) {
  partial::register('taml', function ($file, array $vars = array()) {
    return taml::render($file, $vars);
  });
}

/* EOF: ./stack/library/taml/initialize.php */
