<?php

/**
 * Application initialization
 */

/**
 * @ignore
 */
require __DIR__.DS.'functions'.EXT;

import('www');
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

return function ($bootstrap) {
  (APP_ENV <> 'production') && compile_images();
  if (class_exists('cssp', FALSE)) {
    // default path
    cssp::config('path', APP_PATH.DS.'views'.DS.'assets'.DS.'css');
  };
  return $bootstrap;
};

/* EOF: ./library/application/initialize.php */
