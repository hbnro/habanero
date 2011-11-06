<?php

/**
 * Server library initialization
 */

/**#@+
 * @ignore
 */
require __DIR__.DS.'routing'.EXT;
require __DIR__.DS.'request'.EXT;
require __DIR__.DS.'response'.EXT;

require __DIR__.DS.'session'.EXT;

require __DIR__.DS.'paths'.EXT;
require __DIR__.DS.'actions'.EXT;
require __DIR__.DS.'functions'.EXT;
/**#@-*/

bootstrap::bind(function ($app) {
  // root
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

    if (strpos($_SERVER[$key], INDEX) && is_false($val)) {
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

    if (strpos($_SERVER[$key], INDEX) && is_false($val)) {
      continue;
    }

    $base = $_SERVER[$key];
    break;
  }



  // ----------------------------------------------------------------------------

  // site root
  $base = preg_replace(sprintf('/%s.*$/', preg_quote(INDEX)), '', $base);

  if (($root = server('DOCUMENT_ROOT')) <> '/') {
    $base = str_replace($root, '.', $base);
  }

  define('ROOT', strtr(str_replace(INDEX, '', $base), '\\./', '/'));


  if (option('query')) {
    $parts = '';

    // fallback
    foreach ($_GET as $key => $val) {
      if (substr($key, 0, 1) === '/') {
        unset($_GET[$key]);
        $parts = $key;
        break;
      }
    }
  } else {
    // URL cleanup
    $root   = preg_quote(ROOT, '/');
    $index  = preg_quote(INDEX, '/');
    $suffix = option('rewrite') ? preg_quote(option('suffix'), '/') : '';
    $parts  = preg_replace("/^(?:$root(?:$index)?)?|$suffix$/", '', array_shift(explode('?', $url)));
  }

  define('URI', '/' . trim($parts, '/'));


  if (empty($_SERVER['REQUEST_URI'])) {//FIX
    $_SERVER['REQUEST_URI']  = server('SCRIPT_NAME', server('PHP_SELF'));
    $_SERVER['REQUEST_URI'] .= $query = server('QUERY_STRING') ? "?$query" : '';
  }


  // huh stop!
  if (headers_sent($file, $line)) {
    raise(ln('headers_sent', array('script' => $file, 'number' => $line)));
  }


  // method override
  if ($_method = value($_POST, '_method')) {
    $_SERVER['REQUEST_METHOD'] = strtoupper($_method);

    unset($_POST['_method']);
  }

  // CSRF override
  if ($_token = value($_POST, '_token')) {
    $_SERVER['HTTP_X_CSRF_TOKEN'] = $_POST['_token'];

    unset($_POST['_token']);
  }

  ignore_user_abort(FALSE);

  return function ()
    use($app) {

    // default session hanlder
    // http://php.net/session_set_save_handler

    session_set_save_handler(function () {
      return TRUE;
    }, function () {
      return TRUE;
    }, function ($id) {
      return read(TMP.DS."sess_$id");
    }, function ($id, $data) {
      return write(TMP.DS."sess_$id", $data);
    }, function ($id) {
      return @unlink(TMP.DS."sess_$id");
    }, function ($max) {
      foreach (dir2arr(TMP, "sess_*") as $one) {
        if ((@filemtime(TMP.DS.$one) + $max) < time()) {
          @unlink(TMP.DS.$one);
        }
      }
    });


    // session conf
    if ( ! is_local()) {
      session_set_cookie_params(86400, ROOT, '.' . server());
    }

    session_name('--a-' . preg_replace('/\W/', '-', phpversion()));
    // TODO: BTW with session_id()?
    session_start();


    // expires+hops
    foreach ($_SESSION as $key => $val) {
      if ( ! is_array($val) OR ! array_key_exists('value', $val)) {
        continue;
      }

      if (isset($_SESSION[$key]['expires']) && (time() >= $val['expires'])) {
        unset($_SESSION[$key]);
      }

      if (isset($_SESSION[$key]['hops']) && ($_SESSION[$key]['hops']-- <= 0)) {
        unset($_SESSION[$key]);
      }
    }

    routing::execute($app());
  };
});

/* EOF: ./stack/library/server/initialize.php */
