<?php

require dirname(__DIR__).DS.'application'.EXT;
require APP_PATH.DS.'controllers'.DS.'base'.EXT;

i18n::load_path(dirname(__DIR__).DS.'locale', 'app');

$request = request::methods();

request::implement('dispatch', function (array $params = array())
  use($request) {
  if (is_callable($params['to'])) {
    $request['dispatch']($params);
  } else {
    params($params['matches']);
    application::apply('execute', explode('#', (string) $params['to']));
  }
});

/* EOF: ./library/application/scripts/binding.php */
