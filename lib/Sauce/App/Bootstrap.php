<?php

namespace Sauce\App;

class Bootstrap
{

  public static function initialize(\Closure $lambda)
  {
    $test = strtoupper(PHP_SAPI);

    if ((strpos($test, 'CLI') === FALSE) OR ($test === 'CLI-SERVER')) {
      // static assets
      if ($path = \Postman\Request::value('@')) {
        $test   = \Sauce\App\Assets::read($path);
        $output = new \Postman\Response(200, array('Content-Type' => $test['type']), $test['output']);

        \Sauce\Logger::debug("Serve $path");

        return $output;
      }

      // settings
      \Labourer\Web\Session::initialize();
      ignore_user_abort(FALSE);

      // locales
      if ( ! ($key = option('language'))) {
        $set = \Locale\Base::all();
        $key = key($set);
      }

      @setlocale(LC_ALL, "$key.UTF-8");
      \Locale\Config::set('default', $key);
      \Locale\Base::load_path(path(APP_PATH, 'app', 'locale'));
    }

    \Sauce\Logger::debug('Ready');

    // defaults
    $out = \Sauce\Base::$response;
    $lambda($out);

    if ($action = \Broil\Routing::run()) {
      $uri = \Broil\Config::get('request_uri');
      $method = \Broil\Config::get('request_method');

      \Sauce\Logger::debug("$method $uri");
      \Sauce\Logger::debug("Route $action[match]");

      if ( ! empty($action['before'])) {
        foreach ((array) $action['before'] as $callback) {
          $action = call_user_func($callback, $action);
        }
      }

      params($action['params']);

      if (is_string($action['to'])) {
        if (strpos($action['to'], '://') !== FALSE) {
          $out = new \Postman\Response(redirect($action));
        } elseif (strpos($action['to'], '#') !== FALSE) {
          $cache = empty($action['no-cache']) ? (isset($action['expires']) ? $action['expires'] : option('expires')) : 0;
          $cache = (APP_ENV === 'production') && (\Postman\Request::method() === 'GET') ? $cache : 0;

          @list($controller, $method) = explode('#', (string) $action['to']);

          \Sauce\App\Handler::execute($controller, $method, $cache);
        } else {
          throw new \Exception("Unknown '$action[to]' action");
        }
      } elseif (is_callable($action['to'])) {
        ob_start();
        $tmp = call_user_func($action['to']);
        $old = ob_get_clean();

        if (is_array($tmp)) {
          @list($out->status, $out->headers, $out->response) = $tmp;
        } else {
          $out->status = is_numeric($tmp) ? (int) $tmp : 200;
          $out->response = is_string($tmp) ? $tmp : $old;
        }
      } elseif (is_array($action['to'])) {
        $out = new \Postman\Response($action['to']);
      } else {
        throw new \Exception("Cannot execute '$action[to]'");
      }

      if ( ! empty($action['after'])) {
        foreach ((array) $action['after'] as $callback) {
          $out = call_user_func($callback, $out);
        }
      }

      \Sauce\Logger::debug('Done ', json_encode((array) $out->headers));

      return $out;
    } else {
      throw new \Exception("Route not reach");
    }
  }

}
