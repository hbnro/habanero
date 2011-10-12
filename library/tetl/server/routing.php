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

    static::$routes []= $params;
  }


  /**
   * Run matched routes
   *
   * @return void
   */
  final public static function execute()
  {
    // TODO: still using the same token against XHR?
    define('TOKEN', is_ajax() ? value($_SERVER, 'HTTP_X_CSRF_TOKEN') : sprintf('%d %s', time(), sha1(salt(13))));
    define('CHECK', ! empty($_SESSION['--csrf-token']) ? $_SESSION['--csrf-token'] : NULL);

    option('csrf.protect') && $_SESSION['--csrf-token'] = TOKEN;

    foreach (static::$routes as $params)
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

/* EOF: ./library/tetl/server/routing.php */