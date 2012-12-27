<?php

namespace Sauce;

class Base
{

  public static $autoload = NULL;
  public static $response = NULL;

  private static $loaded = FALSE;
  private static $middleware = array();



  public static function initialize(\Closure $lambda)
  {
    if (static::$loaded) {
      throw new \Exception("Application already loaded");
    }



    // request vars
    params($_REQUEST);


    // configuration
    $config_file = path(APP_PATH, 'config.php');

    is_file($config_file) && config($config_file);


    // timezone
    date_default_timezone_set(option('timezone', 'UTC'));


    // setup
    $test = strtoupper(PHP_SAPI);

    if ((strpos($test, 'CLI') === FALSE) OR ($test === 'CLI-SERVER')) {
      define('INDEX', basename(APP_LOADER));

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

      define('ROOT', ltrim(str_replace(INDEX, '', $base), '.'));


      // URL cleanup
      $root  = preg_quote(ROOT, '/');
      $index = preg_quote(INDEX, '/');

      $parts = explode('?', $url);
      $parts = preg_replace("/^(?:$root(?:$index)?)?$/", '', array_shift($parts));

      define('URI', '/' . trim($parts, '/'));


      if (empty($_SERVER['REQUEST_URI'])) {
        $_SERVER['REQUEST_URI']  = server('SCRIPT_NAME', server('PHP_SELF'));
        $_SERVER['REQUEST_URI'] .= $query = server('QUERY_STRING') ? "?$query" : '';
      }
    } else {
      // TODO: set URI/REQUEST_METHOD from CLI arguments...
      define('INDEX', option('index_file') ?: 'index.php');
      define('ROOT', option('root') ?: '/');
      define('URI', '/');

      $_SERVER['REQUEST_URI'] = URI;
      $_SERVER['REQUEST_METHOD'] = 'GET';
      $_SERVER['DOCUMENT_ROOT'] = APP_PATH;
    }



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


    // routing
    \Broil\Config::set('root', ROOT);
    \Broil\Config::set('index_file', INDEX);
    \Broil\Config::set('rewrite', option('rewrite'));

    \Broil\Config::set('request_uri', URI);
    \Broil\Config::set('request_method', method());


    // in all cases this should be "scheme://hostname[:port]" only?
    $base_url = \Postman\Request::host() ?: option('server_base');

    \Broil\Config::set('server_base', rtrim($base_url, '/'));
    \Broil\Config::set('tld_size', option('tld_size'));


    // templating
    \Tailor\Config::set('cache_dir', path(APP_PATH, 'cache'));

    \Tailor\Config::set('views_dir', path(APP_PATH, 'app', 'views'));
    \Tailor\Config::set('fonts_dir', path(APP_PATH, 'app', 'assets', 'font'));
    \Tailor\Config::set('images_dir', path(APP_PATH, 'app', 'assets', 'img'));
    \Tailor\Config::set('styles_dir', path(APP_PATH, 'app', 'assets', 'css'));
    \Tailor\Config::set('scripts_dir', path(APP_PATH, 'app', 'assets', 'js'));


    // assets
    if (APP_ENV <> 'production') {
      $doc_root = ROOT . '?_=';
    } elseif ( ! ($doc_root = option('asset_host'))) {
      if ($doc_root = option('asset_subdomain')) {
        $doc_root = \Broil\Helpers::reduce($base_url, $doc_root);
      } else {
        $doc_root = $base_url . ROOT . 'static';
      }
    }

    \Tailor\Config::set('fonts_url', "$doc_root/font");
    \Tailor\Config::set('images_url', "$doc_root/img");
    \Tailor\Config::set('styles_url', "$doc_root/css");
    \Tailor\Config::set('scripts_url', "$doc_root/js");

    \Tailor\Base::initialize();


    // caching
    \Cashier\Config::set('cache_dir', TMP);
    \Cashier\Config::set('driver', option('cache', 'php'));


    // database
    \Grocery\Config::set('unserialize', APP_ENV === 'production' ? 'ignore' : 'reset');
    \Servant\Config::set('default', 'sqlite::memory:');


    // connections
    if ($test = option('database')) {
      foreach ($test as $key => $val) {
        \Servant\Config::set($key, $val, APP_ENV === 'production');
      }
    }


    // load routes
    $routes_file = path(APP_PATH, 'config', 'routes.php');

    is_file($routes_file) && require $routes_file;


    // before any initializer!
    foreach (static::$middleware as $callback) {
      $lambda = $callback($lambda);
    }


    // scripts
    $init_path = path(APP_PATH, 'config', 'initializers');

    if (is_dir($init_path)) {
      \IO\Dir::open($init_path, function ($path) {
          require is_dir($path) ? path($path, 'initialize.php') : $path;
        });
    }


    // start
    static::$loaded = TRUE;
    static::$response = new \Postman\Response;

    return \Sauce\App\Bootstrap::initialize($lambda);
  }

  public static function raise($message)
  {
    if ($message instanceof \Exception) {
      $trace = APP_ENV <> 'production' ? $message->getTrace() : array();
      $message = "{$message->getMessage()} <{$message->getFile()}#{$message->getLine()}>";
    } else {
      $trace = APP_ENV <> 'production' ? debug_backtrace() : array();
    }

    $tmp = array();
    $test = strtoupper(PHP_SAPI);

    foreach ($trace as $i => $on) {
      $type   = ! empty($on['type']) ? $on['type'] : '';
      $system = ! empty($on['file']) && strstr($on['file'], 'vendor') ?: FALSE;
      $prefix = ! empty($on['object']) ? get_class($on['object']) : ( ! empty($on['class']) ? $on['class'] : '');
      $call   = $prefix . $type . $on['function'];
      $format_str = ($true = ! empty($on['file'])) ? '%s %s#%d %s()' : '~ %4$s';
      $format_val = sprintf($format_str, $system ? '+' : '-', $true ? $on['file'] : '', $true ? $on['line'] : '', $call);
      $tmp  []= $format_val;
    }

    $trace = array_reverse($tmp);

    if ((strpos($test, 'CLI') === FALSE) OR ($test === 'CLI-SERVER')) {
      $output = \Sauce\Base::$response = new \Postman\Response;
      $status = preg_match('/\b(?:GET|PUT|POST|PATCH|DELETE) \//', $message) ? 404 : 500;

      $output->status = $status;
      $output->headers = array();
      $output->response = $message;

      $vars['status'] = (int) $status;

      // raw headers
      foreach (headers_list() as $one) {
        list($key, $val) = explode(':', $one);
        $vars['headers'][$key] = trim($val);
      }

      // system info
      $vars['host'] = @php_uname('n') ?: sprintf('<%s>', 'Unknown');
      $vars['user'] = 'Unknown';

      foreach (array('USER', 'LOGNAME', 'USERNAME', 'APACHE_RUN_USER') as $key) {
        ($one = @getenv($key)) && $vars['user'] = $one;
      }

      // environment info
      $vars['env'] = $_SERVER;

      foreach (array('PATH_TRANSLATED', 'DOCUMENT_ROOT', 'REQUEST_TIME', 'argc', 'argv') as $key) {
        if (isset($vars['env'][$key])) {
          unset($vars['env'][$key]);
        }
      }

      // received headers
      foreach ((array) $vars['env'] as $key => $val) {
        if (preg_match('/^(?:PHP|HTTP|SCRIPT)/', $key)) {
          if (substr($key, 0, 5) === 'HTTP_') {
            $vars['received'][camelcase(strtolower(substr($key, 5)), TRUE, '-')] = $val;
          }
          unset($vars['env'][$key]);
        }
      }

      try {
        $output = partial('layouts/raising.php', $vars);
      } catch (\Exception $e) { // TODO: refactor?
        $tpl = substr(read(__FILE__), __COMPILER_HALT_OFFSET__);
        $tpl = \Tailor\Base::parse('neddle', $tpl);

        extract($vars);
        $output = eval('?' . ">$tpl");
      }
    } else {
      $trace   = preg_replace('/^/m', '  ', $trace);
      $output .= "\n\n$trace\n";
    }

    echo "$output\n";
  }

  public static function bind(\Closure $middleware)
  {
    static::$middleware []= $middleware;
  }

}

__halt_compiler();
html
  head
    meta(charset="UTF-8")
    title Error
    style
      |p, .debug {
      |  padding: .5em;
      |  font-size: .9em;
      |  background: #ededed;
      |  font-family: Palatino, "Palatino Linotype", "Hoefler Text", Times, "Times New Roman", serif;
      |}
      |pre {
      |  overflow: auto;
      |  padding: 0 .3em !important;
      |  font-family: Monaco, "Bitstream Vera Sans Mono", "Lucida Console", Terminal, monospace;
      |  font-size: .8em;
      |}
      |h3 {
      |  border-bottom: 3px dotted #dedede;
      |  font-family: "Lucida Sans", "Lucida Grande", Lucida, sans-serif;
      |  font-size: 1.3em;
      |}
  body
    p
      ~ $message
    h3 Application
    pre = inspect(array('user' => "$user@$host",
        'route' => URI,
        'method' => method(),
        'params' => params(),
        'bootstrap' => APP_LOADER,
      ))
    - unless empty($headers)
      h3 Response headers
      pre = inspect($headers)
    - unless empty($received)
      h3 Received headers
      pre = inspect($received)
    h3 Includes
    pre = inspect(get_included_files())
    - unless empty($trace)
      h3 Backtrace
      pre = join("\n", $trace)
    - unless empty($env)
      h3 Environment
      pre = inspect($env)
    h3 Configuration
    pre = inspect(config())
    p &mdash; #{round(microtime(TRUE) - BEGIN, 4)}s
