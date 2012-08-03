<?php

/**
 * Application initialization
 */

/**#@+
 * @ignore
 */
import('db');
import('www');
import('assets');
import('partial');
import('a_record');

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
    'subdomain' => '', // TODO: Y U NO CDN?
    'constraints' => array(
      '*path' => '(?:img|css|js)/.+',
    ),
  ));

  // TODO: no further configuration?
  require APP_PATH.DS.'config'.DS.'routes'.EXT;

  // initializers
  if (is_dir($init_path = APP_PATH.DS.'config'.DS.'initializers')) {
    $init_files = dir2arr($init_path, '*'.EXT);
    foreach ($init_files as $path) {
      require is_dir($path) ? $path.DS.'initialize'.EXT : $path;
    }
  }

  require __DIR__.DS.'application'.EXT;
  require APP_PATH.DS.'controllers'.DS.'base'.EXT;


  // JSON response
  application::responds_with('json', function ($data, array $params = array()) {
    $raw = function (&$set, $re) {
      foreach ($set as $k => &$v) {
        if (is_array($v)) {
          $re($v, $re);
        } else {
          $v instanceof a_record && $set[$k] = $v->fields();
        }
      }
    };

    $raw($data, $raw);

    return array(200, json_encode($params + $data), array(
      'content-type' => 'application/json',
    ));
  });


  i18n::load_path(__DIR__.DS.'locale', 'app');


  $request = request::methods();

  request::implement('dispatch', function (array $params = array())
    use($request) {
    if (is_callable($params['to']) OR is_file($params['to'])) {
      return $request['dispatch']($params);
    } else {
      params($params['matches']);
      return application::apply('execute', explode('#', (string) $params['to']));
    }
  });

  return $bootstrap;
});


// logger
logger::implement('write', function ($type, $message) {
  $ip      = request::ip('--');
  $date    = date('Y-m-d H:i:s');
  $message = preg_replace('/[\r\n]+\s*/', ' ', $message);
  write(APP_PATH.DS.'logs'.DS.APP_ENV.'.log', "[$date] [$ip] [$type] $message\n", 1);
});

/**#@-*/

/* EOF: ./library/application/initialize.php */
