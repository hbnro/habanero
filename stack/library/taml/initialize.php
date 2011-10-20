<?php

/**
 * Taml initialization
 */

/**
 * @ignore
 */

require __DIR__.DS.'system'.EXT;

i18n::load_path(__DIR__.DS.'locale', 'taml');


// render callback
if (class_exists('partial')) {
  partial::register('taml', function ($file, array $vars = array()) {
    return taml::render($file, $vars);
  });
}

/* EOF: ./library/tetl/taml/initialize.php */
