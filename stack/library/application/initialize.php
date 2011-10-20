<?php

/**
 * MVC initialization
 */

call_user_func(function () {
  import('server');

  define('CWD', dirname(APP_PATH));

  config(CWD.DS.'config'.EXT);
  config(CWD.DS.'config'.DS.'application'.EXT);
  config(CWD.DS.'config'.DS.'environments'.DS.option('environment').EXT);


  $bootstrap = bootstrap::methods();

  bootstrap::implement('raise', function ($message)
    use($bootstrap) {
    require __DIR__.DS.'scripts'.DS.'raising'.EXT;
  });


  bootstrap::bind(function ($app) {
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

/* EOF: ./stack/library/app/base/initialize.php */
