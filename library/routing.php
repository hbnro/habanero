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

  // params per group
  private static $grouped = array();

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
      'protect'     => FALSE,
      'before'      => array(),
      'after'       => array(),
      'match'       => 'GET /',
      'to'          => 'raise',
    ), $params, static::$grouped);


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
    $test = $params;

    if (isset($params['root'])) {
      unset($params['root']);
    }

    if (isset($params['safe'])) {
      $params['protect'] = (boolean) $params['safe'];
      unset($params['safe']);
    }

    static::$root    = ! empty($test['root']) ? $test['root'] : '/';
    static::$grouped = $params;

    $group();

    static::$grouped = array();
    static::$root    = '/';
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

        if ( ! empty($params['before'])) {
          foreach ((array) $params['before'] as $filter) {
            call_user_func($filter);
          }
        }


        logger::debug("On: ({$params['matches'][0]}) ", ticks($start));

        if ($params['protect']) { // TODO: is this it?
          config('csrf_token', request::is_ajax() ? value($_SERVER, 'HTTP_X_CSRF_TOKEN') : time() . ' ' . sha1(salt(13)));
          config('csrf_check', ! empty($_SESSION['--csrf-token']) ? $_SESSION['--csrf-token'] : NULL);

          ($method <> 'GET') && ! request::is_safe() && raise(ln('invalid_authenticity_token'));

          $_SESSION['--csrf-token'] = option('csrf_token');
        }

        request::dispatch($params);

        if ( ! empty($params['after'])) {
          foreach ((array) $params['after'] as $filter) {
            call_user_func($filter);
          }
        }

        return;
      }
    }

    raise("$method " . URI);
  }

}

/* EOF: ./library/routing.php */
