<?php

/**
 * Application initialization
 */

/**#@+
 * @ignore
 */
import('www');
import('partial');

$bootstrap = core::methods();

// errors
core::implement('raise', function ($message)
  use($bootstrap) {
  require __DIR__.DS.'scripts'.DS.'raising'.EXT;
});


// actions
core::bind(function ($bootstrap) {
  i18n::load_path(APP_PATH.DS.'locale');

  if (APP_ENV === 'development') {
    get('/static/*path', function () {
      return array(
        'output' => assets::read(params('path')),
        'type' => mime(ext(params('path'))),
      );
    }, array(
      'constraints' => array(
        '*path' => '(?:img|css|js)/.+',
      ),
    ));
  }

  routing::load(APP_PATH.DS.'config'.DS.'routes'.EXT, array('safe' => TRUE));

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


// logger
logger::implement('send', function ($type, $message) {
  $date    = date('Y-m-d H:i:s');
  $message = preg_replace('/[\r\n]+\s*/', ' ', $message);
  write(APP_PATH.DS.'logs'.DS.APP_ENV.'.log', "[$date] [$type] $message\n", 1);
});

/**#@-*/

/* EOF: ./library/application/initialize.php */
