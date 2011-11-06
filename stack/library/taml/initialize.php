<?php

/**
 * Taml initialization
 */

call_user_func(function () {
  /**
   * @ignore
   */

  require __DIR__.DS.'taml'.EXT;

  i18n::load_path(__DIR__.DS.'locale', 'taml');


  // render callback
  if (class_exists('partial')) {
    partial::register('taml', function ($file, array $vars = array()) {
      return taml::render($file, $vars);
    });
  }
});

/* EOF: ./stack/library/taml/initialize.php */
