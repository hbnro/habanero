<?php

namespace Sauce;

class Base
{

  public static $autoload = NULL;

  private static $loaded = FALSE;
  private static $middleware = array();



  public static function initialize(\Closure $lambda)
  {
    if (static::$loaded) {
      throw new \Exception("Application already loaded");
    }


    // setup
    $test = strtoupper(PHP_SAPI);

    if ((strpos($test, 'CLI') === FALSE) OR ($test === 'CLI-SERVER')) {
      if (empty($_SERVER['REQUEST_URI'])) {
        $_SERVER['REQUEST_URI']  = server('SCRIPT_NAME', server('PHP_SELF'));
        $_SERVER['REQUEST_URI'] .= $query = server('QUERY_STRING') ? "?$query" : '';
      }
    } else {
      // TODO: how set this?
      $_SERVER['REQUEST_URI'] = '/';
      $_SERVER['REQUEST_METHOD'] = 'GET';
      $_SERVER['DOCUMENT_ROOT'] = APP_PATH;

      // TODO: implement a CLI parser?
      #var_dump($_SERVER['argv']);
      #die();
    }


    // root+uri
    $url = array();

    $url['ORIG_PATH_INFO'] = FALSE;
    $url['REQUEST_URI']    = FALSE;
    $url['SCRIPT_URL']     = TRUE;
    $url['PATH_INFO']      = FALSE;
    $url['PHP_SELF']       = TRUE;

    foreach ($url as $key => $val) {
      if ( ! isset($_SERVER[$key])) {
        continue;
      }

      if (strpos($_SERVER[$key], INDEX) && ($val === FALSE)) {
        continue;
      }

      $url = $_SERVER[$key];
      break;
    }


    $base = array();

    $base['ORIG_SCRIPT_NAME'] = TRUE;
    #$base['SCRIPT_FILENAME'] = TRUE;
    $base['SCRIPT_NAME']      = TRUE;
    $base['PHP_SELF']         = FALSE;

    foreach ($base as $key => $val) {
      if ( ! isset($_SERVER[$key])) {
        continue;
      }

      if (strpos($_SERVER[$key], INDEX) && ($val === FALSE)) {
        continue;
      }

      $base = $_SERVER[$key];
      break;
    }


    // site root
    $base = preg_replace('/' . preg_quote(INDEX) . '.*$/', '', $base);

    if (($root = server('DOCUMENT_ROOT')) <> '/') {
      $base = str_replace($root, '.', $base);
    }

    define('ROOT', strtr(str_replace(INDEX, '', $base), '\\./', '/'));


    // URL cleanup
    $root  = preg_quote(ROOT, '/');
    $index = preg_quote(INDEX, '/');

    $parts = explode('?', $url);
    $parts = preg_replace("/^(?:$root(?:$index)?)?$/", '', array_shift($parts));

    define('URI', '/' . trim($parts, '/'));


    // request vars
    params($_REQUEST);


    // configuration
    $config_file = path(APP_PATH, 'config.php');

    is_file($config_file) && config($config_file);


    // timezone
    date_default_timezone_set(option('timezone', 'UTC'));


    // connections
    if ($test = option('database')) {
      foreach ($test as $key => $val) {
        \Servant\Config::set($key, $val, APP_ENV === 'production');
      }
    }


    // routing
    \Broil\Config::set('root', ROOT);
    \Broil\Config::set('index_file', INDEX);
    \Broil\Config::set('rewrite', option('rewrite'));

    \Broil\Config::set('request_uri', URI);
    \Broil\Config::set('request_method', method());

    \Broil\Config::set('server_name', option('server_name'));
    \Broil\Config::set('subdomain', option('subdomain'));
    \Broil\Config::set('domain', option('domain'));


    // templating
    \Tailor\Config::set('cache_dir', path(APP_PATH, 'cache'));

    \Tailor\Config::set('views_dir', path(APP_PATH, 'views'));
    \Tailor\Config::set('images_dir', path(APP_PATH, 'assets', 'img'));
    \Tailor\Config::set('styles_dir', path(APP_PATH, 'assets', 'css'));
    \Tailor\Config::set('scripts_dir', path(APP_PATH, 'assets', 'js'));

    \Tailor\Config::set('images_url', '/static/img');
    \Tailor\Config::set('styles_url', '/static/css');
    \Tailor\Config::set('scripts_url', '/static/js');

    \Tailor\Base::initialize();


    // web goodies
    \Labourer\Config::set('csrf_salt', '');
    \Labourer\Config::set('csrf_token', '');
    \Labourer\Config::set('csrf_expire', 300);

    \Labourer\Config::set('session_path', ROOT);
    \Labourer\Config::set('session_expire', 3600);

    \Labourer\Config::set('upload_path', path(APP_PATH, 'static', 'uploads'));
    \Labourer\Config::set('upload_type', 'image/*');
    \Labourer\Config::set('upload_min_size', 96);
    \Labourer\Config::set('upload_max_size', 2097152);
    \Labourer\Config::set('upload_extension', array('jpeg', 'jpg', 'png', 'gif', 'ico'));
    \Labourer\Config::set('upload_skip_error', FALSE);
    \Labourer\Config::set('upload_multiple', TRUE);
    \Labourer\Config::set('upload_unique', TRUE);

    \Labourer\Config::set('s3_key', '');
    \Labourer\Config::set('s3_secret', '');
    \Labourer\Config::set('s3_bucket', '');
    \Labourer\Config::set('s3_location', FALSE);
    \Labourer\Config::set('s3_permission', 'public_read');

    \Labourer\Base::initialize();


    // caching
    \Cashier\Config::set('cache_dir', TMP);
    \Cashier\Config::set('driver', option('cache', 'php'));


    // database
    \Grocery\Config::set('unserialize', APP_ENV === 'production' ? 'ignore' : 'reset');
    \Servant\Config::set('default', 'sqlite::memory:');


    // load routes
    $routes_file = path(APP_PATH, 'config', 'routes.php');

    is_file($routes_file) && require $routes_file;


    // scripts
    $init_path = path(APP_PATH, 'config', 'initializers');

    if (is_dir($init_path)) {
      \IO\Dir::open($init_path, function ($path)
        use ($init_path) {
          $path = path($init_path, $path);
          $path = is_dir($path) ? path($path, 'initialize.php') : $path;

          require $path;
        });
    }


    // start
    static::$loaded = TRUE;

    foreach (static::$middleware as $callback) {
      $lambda = $callback($lambda);
    }

    echo \Sauce\App\Bootstrap::instance()->run($lambda);
  }

  public static function raise($message)
  {
    if ($message instanceof \Exception) {
      $trace = APP_ENV <> 'production' ? $message->getTrace() : array();
      $message = "Exception: {$message->getMessage()} ({$message->getFile()}#{$message->getLine()})";
    } else {
      $trace = APP_ENV <> 'production' ? debug_backtrace() : array();
    }

    $app = \Sauce\App\Bootstrap::instance();
    $output = $app->response;

    $output->status = 500;
    $output->headers = array();
    $output->response = $message;

    if (APP_ENV === 'production') {
      // TODO: error view?
    } else {
      $tmp = array();

      foreach ($trace as $i => $on) {
        $type   = ! empty($on['type']) ? $on['type'] : '';
        $system = ! empty($on['file']) && strstr($on['file'], 'vendor') ?: FALSE;
        $prefix = ! empty($on['object']) ? get_class($on['object']) : ( ! empty($on['class']) ? $on['class'] : '');
        $call   = $prefix . $type . $on['function'];
        $format_str = ($true = ! empty($on['file'])) ? '%s %s#%d %s()' : '~ %4$s';
        $format_val = sprintf($format_str, $system ? '+' : '-', $true ? $on['file'] : '', $true ? $on['line'] : '', $call);
        $tmp  []= $format_val;
      }

      $trace = join("\n", array_reverse($tmp));
      $output .= "<pre>$trace</pre>";
    }

    echo "$output\n";
  }

  public static function bind(\Closure $middleware)
  {
    static::$middleware []= $middleware;
  }

}
