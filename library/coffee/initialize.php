<?php

/**
 * Coffee wrapper initialization
 */

if (class_exists('partial')) {
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

  // assets compiler
  assets::compile('coffee', function ($file) {
    return partial::render($file);
  });
}

/* EOF: ./library/coffee/initialize.php */