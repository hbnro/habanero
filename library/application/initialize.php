<?php

/**
 * Application initialization
 */

call_user_func(function () {
  import('server');

  $bootstrap = bootstrap::methods();

  bootstrap::implement('raise', function ($message)
    use($bootstrap) {
    require __DIR__.DS.'scripts'.DS.'raising'.EXT;
  });


  bootstrap::bind(function ($app) {
    i18n::load_path(getcwd().DS.'locale');
    routing::load(getcwd().DS.'routes'.EXT, array('safe' => TRUE));
    require __DIR__.DS.'scripts'.DS.'binding'.EXT;
    return $app;
  });


  route('/all.:type', function () {
    require __DIR__.DS.'scripts'.DS.'serving'.EXT;
  }, array(
    'constraints' => array(
      ':type' => '(css|js)',
    ),
  ));

});

/* EOF: ./library/application/initialize.php */
