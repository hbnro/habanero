<?php

/**
 * Response functions library
 */

/**#@+
  * Expiration values in secs
  */
define('NEVER', time() * 42);
define('YEARLY', 29030400);
define('MONTHLY', 2419200);
define('WEEKLY', 604800);
define('DAILY', 86400);
define('HOURLY', 3600);
define('NOW', - 300);
/**#@-*/


/**
 * Dispatch a specified route
 *
 * @param  mixed  Expression|Function callback
 * @param  mixed  Function callback
 * @param  array  Options hash
 * @return void
 */
function dispatch($route, $to = NULL, array $params = array())
{
  static $defs = array(
            'constraints' => array(),
            'defaults'    => array(),
            'matches'     => array(),
            'locals'      => array(),
            'route'       => '',
            'type'        => '',
            'to'          => '',
          );
  
  
  if (is_assoc($route))
  {
    $params += $route;
  }
  elseif ( ! isset($params['route']))
  {
    $params['route'] = $route;
  }

  if (is_assoc($to))
  {
    $params += $to;
  }
  elseif ( ! isset($params['to']))
  {
    $params['to'] = $to;
  }


  if (empty($params['route']))
  {
    raise(ln('function_or_param_missing', array('name' => __FUNCTION__, 'input' => 'route')));
  }
  
  
  if ( ! isset($params['constraints']))
  {
    $params['constraints'] = array();
  }


  if ( ! isset($params['matches']))
  {
    $params['matches'] = match($params['route'], URI, (array) $params['constraints']);
  }

  $params = extend($defs, $params);


  if ( ! empty($params['matches']))
  {
    if (empty($params['to']))
    {
      raise(ln('function_or_param_missing', array('name' => __FUNCTION__, 'input' => 'to')));
    }


    params((array) $params['defaults'] + (array) $params['matches']);


    ob_start();

    if (is_closure($params['to']))
    {
      if (is_true(call_user_func_array($params['to'], (array) $params)))
      {//FIX
        return TRUE;
      }
    }
    elseif (is_url($params['to']))
    {
      redirect($params);
    }
    elseif (is_file($params['to']))
    {
      if (ext($params['to'], TRUE) == EXT)
      {
        include $params['to'];
      }
      else
      {
        $type   = ! empty($params['type']) ? $params['type'] : mime($params['to']);
        $length = filesize($params['to']);

        header("Content-Length: $length");
        header("Content-Type: $type");

        readfile($params['to']);
      }
    }
    else
    {
      raise(ln('function_or_param_missing', array('name' => __FUNCTION__, 'input' => 'to')));
    }

    $params['output'] = ob_get_clean();
    
    render($params);
  }
}


/**
 * Route redirections
 *
 * @param  mixed Route|Function callback
 * @param  mixed HTTP Status
 * @param  array Hash
 * @return void
 */
function redirect($to = ROOT, $status = NULL, array $params = array())
{
  static $defs = array(
            'headers' => array(),
            'locals'  => array(),
            'status'  => 200,
            'to'      => ROOT,
          );
  
  
  if (is_assoc($to))
  {
    $params += $to;
  }
  elseif ( ! isset($params['to']))
  {
    $params['to'] = $to;
  }

  if (is_assoc($status))
  {
    $params += $status;
  }
  elseif ( ! isset($params['status']))
  {
    $params['status'] = (int) $status;
  }
  
  
  if (empty($params['to']))
  {
    raise(ln('function_or_param_missing', array('name' => __FUNCTION__, 'input' => 'to')));
  }
  

  $params = extend($defs, $params);


  if ($params['to'] === 'back')
  {
    if ( ! ($params['to'] = referer()))
    {
      return FALSE;
    }
  }
  elseif ( ! empty($params['locals']))
  {
    $params['to'] .= ! is_false(strrpos($params['to'], '?')) ? '&' : '?';
    $params['to'] .= http_build_query((array) $params['locals'], NULL, '&');
  }


  status($params['status'], $params['headers']);
  header('Location: ' . str_replace('&amp;', '&', $params['to']), TRUE);
  exit;
}


/**
 * Load partial content/response dinamically
 * 
 * @param  mixed Output
 * @param  array Options hash
 * @return void
 */
function render($content, array $params = array())
{
  static $defs = array(
            'type'    => 'text/html',
            'charset' => CHARSET,
            'headers' => array(),
            'status'  => 200,
            'output'  => '',
          );
  
  
  if (is_assoc($content))
  {
    $params += $content;
  }
  elseif ( ! isset($params['output']))
  {
    $params['output'] = $content;
  }


  if ( ! empty($params['partial']))
  {
    ob_start();

    if ( ! empty($params['locals']))
    {
      extract($params['locals'], EXTR_SKIP | EXTR_REFS);
    }
    
    require $params['partial'];

    $output = ob_get_clean();
    
    return $output;
  }

  if ( ! empty($params['text']))
  {
    $params['output'] = $params['text'];
  }

  
  if (empty($params['output']))
  {
    raise(ln('function_or_param_missing', array('name' => __FUNCTION__, 'input' => 'output')));
  }
  
  
  $params = extend($defs, $params);

  $params['type'] = $params['type'] ?: ini_get('default_mimetype');
  
  if (is_mime($params['type']))
  {
    $params['headers']['content-type'] = $params['type'] . ( ! empty($params['charset']) ? "; charset=$params[charset]" : '');
    $params['headers']['content-length'] = strlen((string) $params['output']);
  }

  status($params['status'], $params['headers']);
  echo $params['output'];
  exit;
}


/**
 * Set status header
 *
 * @param  integer Status number
 * @param  array   Additional headers
 * @return mixed
 */
function status($num = 200, array $headers = array())
{
  static $set = NULL;

  if (is_null($set))
  {
    /**
     * @ignore
     */
    $set = include LIB.DS.'assets'.DS.'scripts'.DS.'status_vars'.EXT;
  }

  if (empty($set['reasons'][$num])) 
  {
    return FALSE;
  }


  if ( ! headers_sent())
  {
    foreach ((array) $headers as $key => $val)
    {
      $key = camelcase($key, TRUE, '-');
      header("$key: $val", TRUE);
    }


    if (substr(PHP_SAPI, 0, 3) === 'cgi')
    {//FIX
      header("Status: $num {$set['reasons'][$num]}", TRUE);
    }
    else
    {
      $protocol = server('SERVER_PROTOCOL');
      header("$protocol $num {$set['reasons'][$num]}", TRUE, $num);
    }
  }
}


/**
 * Force file download
 *
 * @link   http://php.net/manual/en/function.fread.php
 * @param  string  Filepath
 * @param  string  Filename
 * @param  string  Mimetype
 * @param  integer Size limit
 * @return void
 */
function download($path, $name = '', $mime = '', $kbps = 24)
{
  if (headers_sent($file, $line))
  {
    raise(ln('headers_sent', array('script' => $file, 'number' => $line)));
  }

  if ( ! is_file($path))
  {
    raise(ln('file_not_exists', array('name' => $file)));
  }


  $mime   = ! empty($mime) ? $mime : 'application/octet-stream';
  $name   = ! empty($name) ? $name : substr(md5(time()), 0, 7) . basename($path);
  $length = filesize($path);
  
  
  header(sprintf('Content-Disposition: attachment; filename="%s"', $name));
  header(sprintf('Content-Length: %d', $length));
  header(sprintf('Content-Type: %s', $mime));

  header('Content-Transfer-Encoding: binary');
  header('Pragma: no-cache');
  header('Expires: 0');

  if (func_num_args() <= 3)
  {
    readfile($path);
    exit;
  }


  $range  = 0;

  if ($test = server('HTTP_RANGE'))
  {
    list($unit, $orig) = @explode('=', $test, 2);

    if ($unit == 'bytes')
    {
      list($range, $extra) = @explode(',', $orig, 2);
    }
    else
    {
      $range = 0;
    }
  }


  list($start, $end) = @explode('-', $range, 2);

  $end   = empty($end) ? $length - 1 : min(abs((int) $end), $length - 1);
  $start = empty($start) || ($end < abs((int) $start)) ? 0 : max(abs((int) $start), 0);

  if ($start > 0 || $end < ($length - 1))
  {
    status(206);
  }

  header('Accept-Ranges: bytes');
  header("Content-Range: bytes $start-$end/$length");

  $tmp = fopen($path, 'rb');
  fseek($tmp, $start);

  while ( ! feof($tmp))
  {
    if ($start >= $end)
    {
      break;
    }

    set_time_limit(0);

    $bytes  = 1024 * 8;
    $start += $bytes;

    echo fread($tmp, $bytes);

    flush();
  }

  fclose($tmp);
  exit;
}


/**
 * Cookie variable access
 *
 * @param  string  Key or name
 * @param  mixed   Default value
 * @param  integer Expiration time
 * @return mixed
 */
function cookie($key, $value = NULL, $expires = NEVER)
{
  if (func_num_args() === 1)
  {
    return value($_COOKIE, $key);
  }

  setcookie($key, $value, $expires > 0 ? time() + $expires : - 1, ROOT);
}


/**
 * Session variable access
 *
 * @param  string Key or name
 * @param  mixed  Default value
 * @param  array  Options
 * @return mixed
 */
function session($key, $value = '', array $option = array())
{
  $hash =  "--a-session$$key";

  if (func_num_args() === 1)
  {
    if ( ! is_array($test = value($_SESSION, $hash)))
    {
      return FALSE;
    }
    elseif (array_key_exists('value', $test))
    {
      return $test['value'];
    }

    return FALSE;
  }
  elseif (is_string($key) && ! is_num($key))
  {
    if (is_null($value) && isset($_SESSION[$key]))
    {
      unset($_SESSION[$key]);
    }
    else
    {
      if ( ! is_array($option))
      {
        $option = array('expires' => (int) $option);
      }

      if ( ! empty($option['expires']))
      {
        $plus = $option['expires'] < time() ? time() : 0;
        $option['expires'] += $plus;
      }

      $_SESSION[$hash] = $option;
      $_SESSION[$hash]['value'] = $value;
    }
  }
}


/**
 * Flash utility function
 *
 * @param     string Key io name
 * @param     mixed  Default value
 * @staticvar array  Vars bag
 * @return    void
 */
function flash($key = -1, $value = FALSE)
{
  static $output = NULL,
         $set = array();


  if (func_num_args() <= 1)
  {
    if (isset($output[$key]))
    {
      return $output[$key];
    }
    elseif ( ! is_null($output) && ! func_num_args())
    {
      return $output;
    }

    $output = array_filter((array) session('--flash-data'));

    session('--flash-data', array());

    return $output;
  }


  if (is_num($key))
  {
    return FALSE;
  }

  if ( ! isset($set[$key]))
  {
    $set[$key] = $value;
  }
  else
  {
    $set[$key] = (array) $set[$key];
    $set[$key] []= $value;
  }

  session('--flash-data', $set, array(
    'hops' => 1,
  ));
}


/**
 * Try to avoid cache
 *
 * @return void
 */
function nocache()
{
  header('Cache-Control: no-store, no-cache, must-revalidate');
  header('Cache-Control: post-check=0, pre-check=0', FALSE);
  header('Pragma: no-cache');

  header('Last-Modified: ' . date('D, m Y H:i:s \G\M\T', time()));
  header('Expires: ' . date('D, m Y H:i:s \G\M\T', 0));
}

/* EOF: ./core/response.php */