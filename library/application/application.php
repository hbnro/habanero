<?php

/**
 * Application base controller
 */

class application extends prototype
{// TODO: implement... what stuff?

  /**#@+
   * @ignore
   */

  // controller type
  public static $type = '';

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

  // output types
  protected static $respond_to = array();

  /**#@-*/



  /**
   * Output filters
   *
   * @param  string Output type
   * @param  mixed  Function callback
   * @return void
   */
  final public static function output($type, Closure $lambda) {
    static::$respond_to[$type] = $lambda;
  }


  /**
   * Response
   *
   * @param  ...
   * @return array
   */
  final public static function prepare() {
    $out = func_get_args();

    if ( ! empty(static::$respond_to[static::$type])) {
      return call_user_func_array(static::$respond_to[static::$type], $out);
    }
    return $out;
  }

}


// executable actions
application::implement('execute', function ($controller, $action = 'index') {
  $view_file       = findfile(APP_PATH.DS.'views'.DS.$controller, "$action.html*", FALSE, 1);
  $controller_file = APP_PATH.DS.'controllers'.DS.$controller.EXT;

  if ( ! is_file($controller_file)) {
    raise(ln('app.controller_missing', array('name' => $controller_file)));
  }


  /**#@+
   * @ignore
   */
  require $controller_file;
  // TODO: basename or underscore?
  $class_name = basename($controller) . '_controller';


  if ( ! class_exists($class_name)) {
    raise(ln('class_not_exists', array('name' => $class_name)));
  } elseif ( ! $view_file && ! $class_name::defined($action)) {
    raise(ln('app.action_missing', array('controller' => $class_name, 'action' => $action)));
  }

  logger::debug("Start: ($controller#$action)");

  $start = ticks();
  $class_name::defined('init') && $class_name::init();

  if ($class_name::defined($action) && ($test = $class_name::$action())) {
    @list($status, $view, $headers) = $class_name::apply('prepare', $test);
    $class_name::$response = compact('status', 'headers');
  } else {
    $view = partial::render($view_file, (array) $class_name::$view);

    if ($class_name::$layout !== FALSE) {
      $layout_file = "layouts/{$class_name::$layout}";

      $view = partial($layout_file, array(
        'head' => join("\n", $class_name::$head),
        'title' => $class_name::$title,
        'body' => $view,
      ));
    }
  }

  logger::debug("Execute: ($controller#$action) ", ticks($start));

  $output = $class_name::$response;
  $output['output'] = $view;

  return $output;
});

/* EOF: ./library/application/app_controller.php */
