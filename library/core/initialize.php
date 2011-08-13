<?php

/**
 * Initialization script
 */

lambda(function()
{
  // common spec chars
  define('RFC_CHARS', option('allowed_chars', "$\-_.+!*'(),"));


  // default charset
  define('CHARSET', option('encoding', 'UTF-8'));


  // global file permissions
  define('PERMS', option('perms', 0777));
  
  
  // default time zone
  $timezone = option('timezone', 'UTC');
  
  date_default_timezone_set($timezone);
  
  define('TIMEZONE', $timezone);
  
  
  // ----------------------------------------------------------------------------

  // OS temp path
  if ( ! is_dir($temporary_files = option('temporary_files')))
  {
    if (function_exists('sys_get_temp_dir'))
    {
      $temporary_files = @sys_get_temp_dir();
    }
    else
    {
      $old = @tempnam('E', '');
      $temporary_files = dirname($old);
      unlink($old);
    }
  }

  define('TMP', is_dir($temporary_files) && is_writable($temporary_files) ? $temporary_files : LIB.DS.'tmp');

  if ( ! is_dir(TMP))
  {
    mkpath(TMP, PERMS);
  }

  
  
  // initialize language settings
  require LIB.DS.'i18n'.DS.'initialize'.EXT;

  
  // default error and exception hanlders
  set_exception_handler(function($E)
  {
    raise(ln('exception_error', array('message' => $E->getMessage(), 'file' => $E->getFile(), 'number' => $E->getLine())));
  });

  set_error_handler(function($errno, $errmsg, $file, $line, $trace)
  {
    if (($errno & error_reporting()) == $errno)
    {
      raise(ln('error_debug', array('error' => $errmsg, 'file' => $file, 'number' => $line)));
      
      return TRUE;
    }
  });

  
  
  if ( ! IS_CLI)
  {
    global $_SERVER;//FIX


    $url = array();
    
    $url['ORIG_PATH_INFO'] = FALSE;
    $url['REQUEST_URI']    = FALSE;
    $url['SCRIPT_URL']     = TRUE;
    $url['PATH_INFO']      = FALSE;
    $url['PHP_SELF']       = TRUE;

    foreach ($url as $key => $val)
    {
      if ( ! isset($_SERVER[$key]))
      {
        continue;
      }
      
      if (strpos($_SERVER[$key], INDEX) && is_false($val))
      {
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

    foreach ($base as $key => $val)
    {
      if ( ! isset($_SERVER[$key]))
      {
        continue;
      }
      
      if (strpos($_SERVER[$key], INDEX) && is_false($val))
      {
        continue;
      }
      
      $base = $_SERVER[$key];
      break;
    }



    // ----------------------------------------------------------------------------

    // site root
    $base = preg_replace(sprintf('/%s.*$/', preg_quote(INDEX)), '', $base);

    if (($root = server('DOCUMENT_ROOT')) <> '/')
    {
      $base = str_replace($root, '.', $base);
    }
    
    define('ROOT', strtr(str_replace(INDEX, '', $base), '\\./', '/'));


    if (option('query'))
    {
      $parts = '';
      
      // fallback
      foreach ($_GET as $key => $val)
      {
        if (substr($key, 0, 1) === '/')
        {
          unset($_GET[$key]);
          $parts = $key;
          break;
        }
      }
    }
    else
    {
      // URL cleanup
      $root   = preg_quote(ROOT, '/');
      $index  = preg_quote(INDEX, '/');
      $suffix = option('rewrite') ? preg_quote(option('suffix'), '/') : '';
      $parts  = preg_replace("/^(?:$root(?:$index)?)?|$suffix$/", '', array_shift(explode('?', $url)));
    }
    
    define('URI', '/' . trim($parts, '/'));


    if (empty($_SERVER['REQUEST_URI']))
    {//FIX
      $_SERVER['REQUEST_URI']  = server('SCRIPT_NAME', server('PHP_SELF'));
      $_SERVER['REQUEST_URI'] .= $query = server('QUERY_STRING') ? "?$query" : '';
    }
    
    
    
    // default session hanlder
    // http://php.net/session_set_save_handler
  
    session_set_save_handler(function ()
    {
      return TRUE;
    }, function ()
    {
      return TRUE;
    }, function ($id)
    {
      return read(TMP.DS."sess_$id");
    }, function ($id, $data)
    {
      return write(TMP.DS."sess_$id", $data);
    }, function ($id)
    {
      return @unlink(TMP.DS."sess_$id");
    }, function ($max)
    {
      foreach (dir2arr(TMP, "sess_*") as $one)
      {
        if ((filemtime(TMP.DS.$one) + $max) < time())
        {
          unlink(TMP.DS.$one);
        }
      }
    });


    // session conf
    if ( ! is_local())
    {
      session_set_cookie_params(86400, ROOT, '.' . server());
    }
    
    session_name('--a-' . preg_replace('/\W/', '-', phpversion()));
    // TODO: BTW with session_id()?
    session_start();
    
    
    // expires+hops
    foreach ($_SESSION as $key => $val)
    {
      if ( ! is_array($val) OR ! array_key_exists('value', $val))
      {
        continue;
      }
      
      if (isset($_SESSION[$key]['expires']) && (time() >= $val['expires']))
      {
        unset($_SESSION[$key]);
      }
      
      if (isset($_SESSION[$key]['hops']) && ($_SESSION[$key]['hops']-- <= 0))
      {
        unset($_SESSION[$key]);
      }
    }


    // huh stop!
    if (headers_sent($file, $line))
    {
      raise(ln('headers_sent', array('script' => $file, 'number' => $line)));
    }

    
    // built-in CSRF protection
    if (is_safe() && ($_method = post('_method')))
    {
      $_SERVER['REQUEST_METHOD'] = strtoupper($_method);
    }
    
    
    // PUT support
    global $_PUT;
    
    $_PUT = array();
    
    $GLOBALS['_PUT'] =& $_PUT;
    
    
    if (method() === PUT)
    {
      if (server('HTTP_CONTENT_TYPE') === 'application/x-www-form-urlencoded')
      {
        $input = (string) @file_get_contents('php://input');
        parse_str($input, $_PUT);
      }
    }
    
    
    
    $_SESSION['--csrf-token'] = sprintf('%d %s', time(), sha1(salt(13)));
    
    define('TOKEN', $_SESSION['--csrf-token']);
    
    ignore_user_abort(FALSE);
  }
});

/* EOF: ./core/initialize.php */
