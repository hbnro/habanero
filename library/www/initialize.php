<?php

/**
 * Server library initialization
 */

/**#@+
 * @ignore
 */
require __DIR__.DS.'actions'.EXT;
require __DIR__.DS.'dispatch'.EXT;
require __DIR__.DS.'functions'.EXT;
/**#@-*/

core::bind(function ($bootstrap) {
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

    $url = strip_tags($_SERVER[$key]); //FIX?
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


  // URL cleanup
  $root   = preg_quote(ROOT, '/');
  $index  = preg_quote(INDEX, '/');

  $parts  = explode('?', $url);
  $parts  = preg_replace("/^(?:$root(?:$index)?)?$/", '', array_shift($parts));

  define('URI', '/' . trim($parts, '/'));


  if (empty($_SERVER['REQUEST_URI'])) {//FIX?
    $_SERVER['REQUEST_URI']  = server('SCRIPT_NAME', server('PHP_SELF'));
    $_SERVER['REQUEST_URI'] .= $query = strip_tags(server('QUERY_STRING')) ? "?$query" : '';
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

  // default session handler
  // http://php.net/session_set_save_handler

  if ( ! session_id()) {
    // TODO: try another session drivers?
    // session conf
    if ( ! request::is_local()) {
      $host = server();
      $host = is_ip($host) ? $host : ".$host";

      session_set_cookie_params(86400, ROOT, $host);
    }

    session_name('--a-' . preg_replace('/\W/', '-', phpversion()));
    // TODO: BTW with session_id()?
    session_start();
  }


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

  return function ()
    use($bootstrap) {
    routing::execute($bootstrap());
  };
});

/* EOF: ./library/www/initialize.php */
