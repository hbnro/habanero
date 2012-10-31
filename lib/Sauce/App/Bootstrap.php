<?php

namespace Sauce\App;

class Bootstrap
{

  public $response = NULL;

  private static $obj = NULL;



  private function __construct()
  {
    // huh stop!
    if (headers_sent($file, $line)) {
      throw new \Exception("Headers already sent on $file, line $line");
    }


    // static assets
    get('/static/@path', function () {
      $test = \Sauce\App\Assets::read(params('path'));
      return array(200, array('Content-Type' => $test['type']), $test['output']);
    }, array(// TODO: Y U NO CDN?
      'subdomain' => '',
      'constraints' => array(
        '@path' => '(img|css|js)/.+',
      ),
    ));


    // settings
    \Labourer\Web\Session::initialize();
    ignore_user_abort(FALSE);
  }


  public function run(\Closure $lambda)
  {
    // defaults
    $out = $this->response;

    $out->response = 'Not Found';
    $out->headers = array();
    $out->status = 404;

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
      throw new \Exception("Route not found");
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

