<?php

namespace Sauce\App;

class Bootstrap
{

  public $response = NULL;

  private static $obj = NULL;



  private function __construct()
  {
    $test = strtoupper(PHP_SAPI);

    if ((strpos($test, 'CLI') === FALSE) OR ($test === 'CLI-SERVER')) {
      // TODO: what is happening?

      // static assets
      if ($path = \Postman\Request::value('_')) {
        $test   = \Sauce\App\Assets::read($path);
        $output = new \Postman\Response(200, array('Content-Type' => $test['type']), $test['output']);

        echo $output;
        exit;
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
  }


  public function run(\Closure $lambda)
  {
    // defaults
    $out = $this->response;
    $lambda($out);


    if ($action = \Broil\Routing::run()) {
      if ( ! empty($action['before'])) {
        foreach ((array) $action['before'] as $callback) {
          $action = call_user_func($callback, $action);
        }
      }


      params($action['params']);

      if (is_string($action['to'])) {
        if (strpos($action['to'], '://') !== FALSE) {
          $out->redirect($action);
        } elseif (strpos($action['to'], '#') !== FALSE) {
          $cache = (APP_ENV === 'production') && (\Postman\Request::method() === 'GET') && empty($action['no-cache']);
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
      } else {
        throw new \Exception("Cannot execute '$action[to]'");
      }


      if ( ! empty($action['after'])) {
        foreach ((array) $action['after'] as $callback) {
          $out = call_user_func($callback, $out);
        }
      }

      return $out;
    } else {
      throw new \Exception("Route not reach");
    }
  }

  public static function instance()
  {
    if ( ! static::$obj) {
      static::$obj = new static;
      static::$obj->response = new \Postman\Response;
    }
    return static::$obj;
  }

}

