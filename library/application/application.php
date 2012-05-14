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
  public static function to_json($obj, $status = 200, $raw = FALSE) {
    return array($status, $raw ? $obj : json_encode($obj), array(
      'content-type' => 'application/json',
    ));
  }


  /**
   * Executable actions
   *
   * @param  string Controller
   * @param  string Action
   * @return void
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
    // TODO: basename or underscore?
    $class_name  = basename($controller) . '_controller';


    if ( ! class_exists($class_name)) {
      raise(ln('class_not_exists', array('name' => $class_name)));
    } elseif ( ! $class_name::defined($action)) {
      raise(ln('app.action_missing', array('controller' => $class_name, 'action' => $action)));
    }

    debug("Start: ($controller#$action)");

    $start = ticks();
    $class_name::defined('init') && $class_name::init();

    if ($test = $class_name::$action()) {
      @list($status, $view, $headers) = $test;
      $class_name::$response = compact('status', 'headers');
    } else {
      $view = partial("$controller/$action.html", (array) $class_name::$view);

      if ( ! is_false($class_name::$layout)) {
        $layout_file = "layouts/{$class_name::$layout}";

        $view = partial($layout_file, array(
          'head' => join("\n", $class_name::$head),
          'title' => $class_name::$title,
          'body' => $view,
        ));
      }
    }

    debug("Execute: ($controller#$action)\n", '  ', ticks($start));

    $output = $class_name::$response;
    $output['output'] = $view;

    return $output;
  }
}

/* EOF: ./library/application/app_controller.php */
