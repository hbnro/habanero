<?php

require dirname(__DIR__).DS.'application'.EXT;
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

/* EOF: ./library/application/scripts/binding.php */
