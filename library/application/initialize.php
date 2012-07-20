<?php

/**
 * Application initialization
 */

/**#@+
 * @ignore
 */
import('www');
import('assets');
import('partial');

$bootstrap = core::methods();

// errors
core::implement('raise', function ($message)
  use($bootstrap) {
  $error_status = 500;

  switch (APP_ENV) {
    case 'production';
      if (preg_match('/^(?:GET|PUT|POST|DELETE)\s+\/.+?$/', $message)) {
        $error_status = 404;
      }
    break;
    default;
      $bootstrap['raise']($message);
    break;
  }

  $methods[500] = 'unknown';
  $methods[404] = 'not_found';

  $output = application::execute('error', $methods[$error_status]);

  response($output);
  // TODO: right?
  exit;
});


// actions
core::bind(function ($bootstrap) {
  i18n::load_path(APP_PATH.DS.'locale');

  get('/static/*path', function () {
    return assets::read(params('path'));
  }, array(
    'constraints' => array(
      '*path' => '(?:img|css|js)/.+',
    ),
  ));


  routing::load(APP_PATH.DS.'config'.DS.'routes'.EXT, array('safe' => TRUE));

  // initializers
  if (is_dir($init_path = APP_PATH.DS.'config'.DS.'initializers')) {
    $init_files = dir2arr($init_path, '*'.EXT);
    foreach ($init_files as $path) {
      require is_dir($path) ? $path.DS.'initialize'.EXT : $path;
    }
  }

  require __DIR__.DS.'application'.EXT;
  require APP_PATH.DS.'controllers'.DS.'base'.EXT;

  i18n::load_path(dirname(__DIR__).DS.'locale', 'app');

  $request = request::methods();

  request::implement('dispatch', function (array $params = array())
    use($request) {
    if (is_callable($params['to'])) {
      return $request['dispatch']($params);
    } else {
      params($params['matches']);
      return application::apply('execute', explode('#', (string) $params['to']));
    }
  });


  application::output('json', function ($obj, $status = 200, $raw = FALSE) {
    return array($status, $raw ? $obj : json_encode($obj), array(
      'content-type' => 'application/json',
    ));
  });

  return $bootstrap;
});


// logger
logger::implement('write', function ($type, $message) {
  $date    = date('Y-m-d H:i:s');
  $message = preg_replace('/[\r\n]+\s*/', ' ', $message);
  write(APP_PATH.DS.'logs'.DS.APP_ENV.'.log', "[$date] [$type] $message\n", 1);
});

/**#@-*/

/* EOF: ./library/application/initialize.php */
