<?php

/**
 * Routing
 */

class routing
{

  /**#@+
   * @ignore
   */

  // mount root
  private static $root = '/';

  // routing stack
  private static $routes = array();

  // CSRF protection
  private static $protect = FALSE;

  /**#@-*/



  /**
   * Register routes
   *
   * @param  array Options hash
   * @return void
   */
  final public static function bind(array $params = array()) {
    $params = array_merge(array(
      'constraints' => array(),
      'defaults'    => array(),
      'protect'     => static::$protect,
      'match'       => 'GET /',
      'to'          => 'raise',
    ), $params);


    $test            = preg_split('/\s+/', $params['match']);
    $test[1]         = rtrim(static::$root, '/') . $test[1];
    $params['match'] = join(' ', $test);

    $test[1] <> '/' && $params['match'] = rtrim($params['match'], '/');

    if ( ! empty($params['path'])) {
      $parts = explode(' ', $params['match']);
      url_for::register($params['path'], end($parts));
    }

    static::$routes []= $params;
  }


  /**
   * Route mounting
   *
   * @param  string Path
   * @param  array  Options hash
   * @return void
   */
  final public static function load($path, array $params = array()) {
    is_file($path) && static::mount(function ()
      use($path) {
      require $path;
    }, $params);
  }


  /**
   * Route mounting
   *
   * @param  mixed Function callback
   * @param  array Options hash
   * @return void
   */
  final public static function mount(Closure $group, array $params = array()) {
    $params = array_merge(array(
      'root' => '/',
      'safe' => FALSE,
    ), $params);

    static::$root    = $params['root'];
    static::$protect = $params['safe'];

    $group();

    static::$root    = '/';
    static::$protect = FALSE;
  }


  /**
   * Run matched routes
   *
   * @return void
   */
  final public static function execute() {
    $start  = ticks();
    $method = request::method();

    foreach (static::$routes as $params) {
      $expr = "^$params[match]$";
      $test = "$method " . URI;

      $params['matches'] = match($expr, $test, (array) $params['constraints']);

      if ($params['matches']) {
        if ($params['to'] === '.') {
          $params['to'] = ROOT;
        }


        debug("On: ({$params['matches'][0]}) ", ticks($start));

        if ($params['protect']) {
          $old_token = value($_SERVER, 'HTTP_X_CSRF_TOKEN');
          $new_token = sprintf('%d %s', time(), sha1(salt(13)));

          config('csrf_token', request::is_ajax() ? $the_token : $new_token);// TODO: huh?
          config('csrf_check', ! empty($_SESSION['--csrf-token']) ? $_SESSION['--csrf-token'] : NULL);

          $_SESSION['--csrf-token'] = option('csrf_token');
        }

        request::dispatch($params);
      }
    }

    raise("$method " . URI);
  }

}

/* EOF: ./library/routing.php */
