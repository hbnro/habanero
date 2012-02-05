<?php

/**
 * Application base controller
 */

class application extends prototype
{// TODO: implement... what stuff?

  /**#@+
   * @ignore
   */

  // view instance vars
  public static $view = array();

  // head hooks?
  public static $head = array();

  // default title
  public static $title = '';

  // default assets
  public static $source = '';

  // default layout
  public static $layout = 'default.html';

  // output response
  public static $response = array(
                  'type' => 'text/html',
                  'status' => 200,
                );

  /**#@-*/



  /**
   * JSON output
   *
   * @param  mixed   Object|Array
   * @param  integer Response status
   * @return void
   */
  public static function to_json($obj, $status = 200) {
    return array($status, json_encode($obj), array(
      'content-type' => 'application/json',
    ));
  }


  /**
   *
   */
  public static function execute($controller, $action = 'index') {
    $controller_file = APP_PATH.DS.'controllers'.DS.$controller.EXT;

    if ( ! is_file($controller_file)) {
      raise(ln('app.controller_missing', array('name' => $controller_file)));
    }


    /**#@+
     * @ignore
     */
    require $controller_file;

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
        $params = array();

        $class_name::$source && $params['locals'] = array('src' => $class_name::$source);

        $class_name::$head []= tag('meta', array('name' => 'csrf-token', 'content' => TOKEN));
        $class_name::$head []= tag('link', array('rel' => 'stylesheet', 'href' => url_for('/all.css', $params)));

        assets::inline(tag('script', array('src' => url_for('/all.js', $params))), 'body');

        $layout_file = "layouts/{$class_name::$layout}";

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

}

/* EOF: ./library/application/app_controller.php */
