<?php

namespace Sauce\App;

class Handler
{

  public static function execute($controller, $action = 'index', $cache = FALSE)
  {
    $ttl = option('expires', 300);
    $out = \Sauce\App\Bootstrap::instance()->response;
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

      $base_name = camelcase(basename(APP_PATH), TRUE, '\\');
      $class_name = "\\$base_name\\App\\" . camelcase($controller, TRUE, '\\');

      if ( ! class_exists($class_name)) {
        throw new \Exception("Missing '$class_name' class");
      }


      $app = new $class_name;
      $app->view = new \Sauce\App\View;

      $type = params('format');

      if ($type && ! in_array($type, $app->responds_to)) {
        throw new \Exception("Unsupported '$type' response");
      }


      $handle = new \Postman\Handle($app, $type);

      foreach (get_class_methods($app) as $callback) {
        if (substr($callback, 0, 3) === 'as_') {
          $handle->register('json', function ()
            use ($app, $callback) {
            return call_user_func_array(array($app, $callback), func_get_args());
          });
        }
      }

      $test = $handle->execute($action);
      @list($out->status, $out->headers, $out->response) = $test;

      if ( ! $out->response) {
        $out->response = partial("$controller/$action.php", $app->view->all());

        if ($app->layout) {
          $layout_file = "layouts/$app->layout.php";

          $out->response = partial($layout_file, array(
            'head' => join("\n", $app->head),
            'title' => $app->title,
            'body' => $out->response,
            'view' => $app->view,
          ));
        }
      }

      \Cashier\Base::store($hash, array($out->status, $out->headers, $out->response), $ttl);
    }
    return $out;
  }

}
