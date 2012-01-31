<?php

/**
 * Application initialization
 */

import('server');
import('partial');

$bootstrap = app::methods();

app::implement('raise', function ($message)
  use($bootstrap) {
  require __DIR__.DS.'scripts'.DS.'raising'.EXT;
});


app::bind(function ($bootstrap) {
  i18n::load_path(APP_PATH.DS.'locale');
  routing::load(APP_PATH.DS.'routes'.EXT, array('safe' => TRUE));

  require __DIR__.DS.'scripts'.DS.'binding'.EXT;
  return $bootstrap;
});


route('/all.:type', function () {
  require __DIR__.DS.'scripts'.DS.'serving'.EXT;
}, array(
  'constraints' => array(
    ':type' => '(css|js)',
  ),
));

/* EOF: ./library/application/initialize.php */
