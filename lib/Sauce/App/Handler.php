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

      $base_name = classify(basename(APP_PATH));
      $class_name = classify($controller);

      if ( ! class_exists($class_name)) {
        throw new \Exception("Missing '$class_name' class");
      }


      $app = new $class_name;
      $app->view = new \Sauce\App\View;

      $test = get_class_methods($app);
      $type = params('format') ?: 'html';

      if ($type && ! in_array($type, $app->responds_to)) {
        throw new \Exception("Unsupported '$type' response");
      }

      $handle = new \Postman\Handle($app, $type);

      foreach ($test as $callback) {
        if (substr($callback, 0, 3) === 'as_') {
          $handle->register(substr($callback, 3), function ()
            use ($app, $callback) {
              return call_user_func_array(array($app, $callback), func_get_args());
            });
        }
      }


      $test = $handle->exists($action) ? $handle->execute($action) : array();
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
