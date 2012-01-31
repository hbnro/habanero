<?php

/**
 * Application initialization
 */

import('server');
import('partial');

$bootstrap = app::methods();

// errors
app::implement('raise', function ($message)
  use($bootstrap) {
  require __DIR__.DS.'scripts'.DS.'raising'.EXT;
});


// actions
app::bind(function ($bootstrap) {
  i18n::load_path(APP_PATH.DS.'locale');
  routing::load(APP_PATH.DS.'routes'.EXT, array('safe' => TRUE));

  // initializers
  if (is_dir($init_path = APP_PATH.DS.'config'.DS.'initializers')) {
    foreach (dir2arr($init_path, '*'.EXT) as $file) {
      require $file;
    }
  }

  require __DIR__.DS.'scripts'.DS.'binding'.EXT;
  return $bootstrap;
});


// filters
assets::compile('php', function ($file) {
  return partial::render($file);
});


// assets
route('/all.:type', function () {
  require __DIR__.DS.'scripts'.DS.'serving'.EXT;
}, array(
  'constraints' => array(
    ':type' => '(css|js)',
  ),
));

/* EOF: ./library/application/initialize.php */
