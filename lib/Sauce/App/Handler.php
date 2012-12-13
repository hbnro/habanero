<?php

namespace Sauce\App;

class Handler
{

  public static function execute($controller, $action = 'index', $cache = FALSE)
  {
    $ttl = option('expires', 33);
    $out = \Sauce\Base::$response;
    $hash = md5(URI . '?' . server('QUERY_STRING') . '#' . session_id());

    if ($ttl && $cache && ($test = \Cashier\Base::fetch($hash))) {
      @list($out->status, $out->headers, $out->response) = $test;
    } else {
      $controller_path = path(APP_PATH, 'app', 'controllers');
      $controller_base = path($controller_path, 'base.php');
      $controller_file = path($controller_path, "$controller.php");

      if ( ! is_file($controller_file)) {
        throw new \Exception("Missing '$controller_file' file");
      }


      is_file($controller_base) && require $controller_base;
      require $controller_file;

      $base_name = classify(basename(APP_PATH));
      $class_name = classify($controller);

      if ( ! class_exists($class_name)) {
        throw new \Exception("Missing '$class_name' class");
      }


      $app = new $class_name;
      $klass = new \ReflectionClass($app);

      $type = params('format');
      $params = $klass->getStaticProperties();
      $methods = $klass->getMethods(\ReflectionMethod::IS_STATIC);

      if ($type && ! in_array($type, $params['responds_to'])) {
        throw new \Exception("Unknown response for '$type' type");
      }


      $handle = new \Postman\Handle($app, $type);

      foreach ($methods as $callback) {
        $one = $callback->getName();

        if (substr($one, 0, 3) === 'as_') {
          $handle->register(substr($one, 3), function ()
            use ($app, $callback) {
              return call_user_func_array($callback->getClosure(), func_get_args());
            });
        }
      }



      $test = $handle->exists($action) ? $handle->execute($action) : array();
      @list($out->status, $out->headers, $out->response) = $test;
      $vars = (array) $class_name::$view;

      if ( ! $out->response) {
        $out->response = partial("$controller/$action.php", $vars);

        if ($params['layout']) {
          $layout_file = "layouts/$params[layout].php";

          $out->response = partial($layout_file, array(
            'head' => join("\n", $params['head']),
            'title' => $params['title'],
            'body' => $out->response,
            'view' => $vars,
          ));
        }
      }

      \Cashier\Base::store($hash, array($out->status, $out->headers, $out->response), $ttl);
    }
    return $out;
  }

}
