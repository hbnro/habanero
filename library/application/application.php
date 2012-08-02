<?php

/**
 * Application base controller
 */

class application extends prototype
{// TODO: implement... what stuff?

  /**#@+
   * @ignore
   */

  // output callbacks
  protected static $stack = array();

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
  public static $responds_to = array('html');

  /**#@-*/



  /**
   * Prepare output
   *
   * @param  string Format
   * @param  mixed  Output
   * @param  array  Options
   * @return mixed
   */
  public static function serve($format, $data, array $params = array()) {
    if (in_array($format, static::$respond_to)) {
      if (is_object($data)) {
        $obj  = $data;
        $data = array();

        $data[get_class($obj)] = $obj;
      }

      if ( ! empty(static::$stack[$format])) {
        return call_user_func(static::$stack[$format][0], $data, $params);
      } elseif ($format === 'html') {
        static::$view += $data;
        return;
      }
    }

    raise(ln('app.unknown_type', compact('format')));
  }


  /**
   * Response registry
   *
   * @param  mixed Format(s)
   * @param  mixed Function callback
   * @param  array Options
   * @return mixed
   */
  public static function responds_with($format, Closure $lambda, array $params = array()) {
    if (is_array($format)) {
      foreach ($format as $one) {
        static::respond_with($one, $lambda, $params);
      }
    } else {
      static::$stack[$format] = array($lambda, $params);
    }
  }
}


// executable actions
application::implement('execute', function ($controller, $action = 'index') {
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
  }


  logger::debug("Start: ($controller#$action)");

  $start = ticks();
  $class_name::defined('init') && $class_name::init();

  $params  = $class_name::defined($action) ? $class_name::$action() : NULL;
  $content = $class_name::$response;

  if (is_array($params)) {
    array_unshift($params, params('format') ?: 'html');

    $test = $class_name::apply('serve', $params);

    if (is_array($test)) {
      @list($status, $output, $headers) = $test;
      $content = compact('status', 'output', 'headers');
    }
  }


  // TODO: find a better way?
  if ( ! isset($content['output'])) {
    $content['output'] = partial($controller.DS."$action.html", $class_name::$view);

    if ($class_name::$layout !== FALSE) {
      $layout_file = "layouts/{$class_name::$layout}";

      $content['output'] = partial($layout_file, array(
        'head' => join("\n", $class_name::$head),
        'title' => $class_name::$title,
        'body' => $content['output'],
      ));
    }
  }


  logger::debug("Execute: ($controller#$action) ", ticks($start));

  return $content;
});

/* EOF: ./library/application/application.php */
