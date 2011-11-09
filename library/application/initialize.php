<?php

/**
 * Application initialization
 */

call_user_func(function () {
  import('server');

  chdir(dirname(APP_PATH).DS.'app');

  config(getcwd().DS.'config'.DS.'application'.EXT);
  config(getcwd().DS.'config'.DS.'environments'.DS.option('environment').EXT);

  $bootstrap = bootstrap::methods();

  bootstrap::implement('raise', function ($message)
    use($bootstrap) {
    require __DIR__.DS.'scripts'.DS.'raising'.EXT;
  });


  bootstrap::bind(function ($app) {
    i18n::load_path(getcwd().DS.'locale');
    config('import_path', getcwd().DS.'lib');
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
