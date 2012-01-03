<?php

require dirname(__DIR__).DS.'app_controller'.EXT;
i18n::load_path(__DIR__.DS.'locale', 'app');

$request = request::methods();

request::implement('dispatch', function (array $params = array())
  use($request) {
  if (is_callable($params['to'])) {
    $request['dispatch']($params);
  } else {
    params($params['matches']);

    list($controller, $action) = explode('#', (string) $params['to']);

    $controller_file = APP_PATH.DS.'controllers'.DS.$controller.EXT;

    if ( ! is_file($controller_file)) {
      raise(ln('app.controller_missing', array('name' => $controller_file)));
    }


    /**#@+
     * @ignore
     */

    require APP_PATH.DS.'controllers'.DS.'base'.EXT;
    require APP_PATH.DS.'library'.DS.'helpers'.EXT;
    require $controller_file;

    /**#@-*/

    $class_name  = $controller . '_controller';


    if ( ! class_exists($class_name)) {
      raise(ln('class_not_exists', array('name' => $class_name)));
    } elseif ( ! $class_name::defined($action)) {
      raise(ln('app.action_missing', array('controller' => $class_name, 'action' => $action)));
    }


    $class_name::defined('init') && $class_name::init();

    if ($test = $class_name::$action()) {
      @list($status, $view, $headers) = $test;
      $class_name::$response = compact('status', 'headers');
    } else {
      $view = partial("$controller/$action.html", (array) $class_name::$view);

      if ( ! is_false($class_name::$layout)) {
        $class_name::$head []= tag('meta', array('name' => 'csrf-token', 'content' => TOKEN));
        $class_name::$head []= tag('link', array('rel' => 'stylesheet', 'href' => url_for('/all.css')));

        $layout_file = "layouts/{$class_name::$layout}";

        assets::inline(tag('script', array('src' => url_for('/all.js'))), 'body');

        $view = partial($layout_file, array(
          'head' => join("\n", $class_name::$head),
          'title' => $class_name::$title,
          'body' => $view,
        ));
      }
    }

    $output = $class_name::$response;
    $output['output'] = $view;
    response($output);
  }
});

/* EOF: ./library/application/scripts/binding.php */
