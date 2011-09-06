<?php

/**
 * Routing
 */

class routing extends prototype
{

  /**#@+
   * @ignore
   */

  // routing stack
  private static $routes = array();

  /**#@-*/



  /**
   * Register hooks
   *
   * @param  array Options hash
   * @return void
   */
  final public static function bind(array $params = array())
  {
    $params = array_merge(array(
      'constraints' => array(),
      'defaults'    => array(),
      'match'       => 'GET /',
      'to'          => 'raise',
    ), $params);

    routing::$routes []= $params;
  }


  /**
   * Run matched routes
   *
   * @return void
   */
  final public static function execute()
  {
    foreach (routing::$routes as $params)
    {
      $expr = "^$params[match]$";
      $test = request::method() . ' ' . URI;

      $params['matches'] = match($expr, $test, (array) $params['constraints']);

      if ($params['matches'])
      {
        if ($params['to'] === '.')
        {
          $params['to'] = ROOT;
        }

        request::dispatch($params);
      }
    }

    raise(request::method() . ' ' . URI);
  }

}

/* EOF: ./lib/tetl/server/routing.php */
