<?php

namespace Sauce\App;

class Handler
{

  public static function execute($controller, $action = 'index', $cache = -1)
  {
    $out = \Sauce\Base::$response;
    $hash = md5(URI . '?' . server('QUERY_STRING') . '#' . session_id());

    \Sauce\Logger::debug("Executing $controller#$action");

    if (($cache > 0) && ($test = \Cashier\Base::fetch($hash))) {
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
      $handle = new \Postman\Handle($app, $type);
      $methods = $klass->getMethods(\ReflectionMethod::IS_STATIC);

      foreach ($methods as $callback) {
        $fn = $callback->getName();

        if (substr($fn, 0, 3) === 'as_') {
          $handle->register(substr($fn, 3), function ()
            use ($class_name, $fn) {
              return call_user_func_array("$class_name::$fn", func_get_args());
            });
        }
      }



      $test = $handle->exists($action) ? $handle->execute($action) : array();

      $vars = (array) $class_name::$view;
      $params = $klass->getStaticProperties();

      if ($type) {
        if ( ! in_array($type, $params['responds_to'])) {
          throw new \Exception("Unknown response for '$type' type");
        }

        \Sauce\Logger::debug("Using response for '$type' type");

        $test = $handle->responds($test[2], $params);
      }

      @list($out->status, $out->headers, $out->response) = $test;


      if ($out->response === NULL) {
        \Sauce\Logger::debug("Rendering view $controller/$action.php");

        $out->response = partial("$controller/$action.php", $vars);

        if ($params['layout']) {
          \Sauce\Logger::debug("Using layout layouts/$params[layout].php");

          $layout_file = "layouts/$params[layout].php";

          $out->response = partial($layout_file, array(
            'head' => join("\n", $params['head']),
            'title' => $params['title'],
            'body' => $out->response,
            'view' => $vars,
          ));
        }
      }

      if ($cache > 0) {
        \Sauce\Logger::debug("Caching for $cache seconds ($controller#$action)");
        \Cashier\Base::store($hash, array($out->status, $out->headers, $out->response), $cache);
      }
    }
    return $out;
  }

}
