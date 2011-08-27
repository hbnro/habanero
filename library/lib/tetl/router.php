<?php

/**
 * Basic request/response functions
 */

/**#@+
  * HTTP methods
  */
define('GET', 'GET');
define('PUT', 'PUT');
define('POST', 'POST');
define('DELETE', 'DELETE');
/**#@-*/

/**
 * Test the expression against URI, if it match execute the callback
 *
 * @param  mixed Expression|Function callback
 * @param  mixed Function callback
 * @param  array Options hash
 * @return mixed
 */
function route($match, $to = NULL, array $params = array())
{
  if (is_assoc($match))
  {
    $params += $match;
  }
  elseif ( ! isset($params['match']))
  {
    $params['match'] = $match;
  }

  if (is_assoc($to))
  {
    $params += $to;
  }
  elseif ( ! isset($params['to']))
  {
    $params['to'] = $to;
  }


  foreach (array('GET', 'POST', 'PUT' , 'DELETE') as $method)
  {
    $key = strtolower($method);

    if ( ! empty($params[$key]))
    {
      $params['match'] = $method . ' ' . $params[$key];
    }
  }


  if (empty($params['match']))
  {
    raise(ln('function_or_param_missing', array('name' => __FUNCTION__, 'input' => 'match')));
  }


  $params['match'] = trim($params['match']);

  if (is_false(strpos($params['match'], ' ')))
  {
    $params['match'] = 'GET ' . $params['match'];
  }

  $params += array(
    'constraints' => array(),
    'defaults'    => array(),
    'route'       => $params['match'],
    'to'          => 'raise',
  );



  $expr = "^$params[route]$";
  $test = method() . ' ' . URI;

  $params['matches'] = match($expr, $test, (array) $params['constraints']);

  if ( ! empty($params['matches']))
  {
    if ($params['to'] === '.')
    {
      $params['to'] = ROOT;
    }


    trigger(__FUNCTION__, TRUE, $params);

    if (empty($params['to']) OR
     ! (is_callable($params['to']) OR
        is_file($params['to']) OR
        is_url($params['to'])))
    {
      raise(ln('function_or_param_missing', array('name' => __FUNCTION__, 'input' => 'to')));
    }

    params($params['matches']) && dispatch($params);
  }
}


/**
 * Function handler for global hash params
 *
 * @param  mixed Identifier|Hash
 * @param  mixed Default value
 * @return mixed
 */
function params($key = NULL, $default = FALSE)
{
  static $set = array();

  if ( ! func_num_args())
  {
    return $set;
  }
  elseif (is_array($key))
  {
    foreach ($key as $a => $value)
    {
      if (is_num($a))
      {
        continue;
      }

      $set[trim($a)] = $value;
    }

    return TRUE;
  }
  elseif ( ! is_num($key))
  {
    return ! empty($set[$key]) ? $set[$key] : $default;
  }

  return FALSE;
}


/**
 * Segments part
 *
 * @staticvar array Parts bag
 * @return    array
 */
function parts()
{
  static $test = NULL;

  if ( ! is_array($test))
  {
    $test = explode('/', trim(URI, '/'));


    foreach ($test as $key => $val)
    {
      $test[$key] = $val;
    }
  }
  return $test;
}


/**
 * Single segment
 *
 * @param  integer Index key
 * @param  mixed   Default value
 * @return string
 */
function segment($index = 1, $default = FALSE)
{
  $set = parts();


  if ( ! $index)
  {
    return sizeof($set);
  }
  elseif ($index < 0)
  {
    $index = sizeof($set) + 1 + $index;
  }

  $output = ! empty($set[$index - 1]) ? $set[$index - 1] : $default;

  return $output;
}


/**
 * Associative segments
 *
 * @param  integer Index key
 * @param  mixed   Default value
 * @return array
 */
function assoc($index = 1, $default = FALSE)
{
  $set    = parts();
  $output = array();

  $index  = $index - 1;
  $length = sizeof($set);


  for (; $index < $length; $index += 2)
  {
    $value = isset($set[$index + 1]) ? $set[$index + 1] : $default;
    $output[$set[$index]] = $value;
  }

  return $output;
}


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

  $params += $defs;


  if ( ! empty($params['matches']))
  {
    if (empty($params['to']))
    {
      raise(ln('function_or_param_missing', array('name' => __FUNCTION__, 'input' => 'to')));
    }


    params((array) $params['defaults'] + (array) $params['matches']);

    $content['output']  = '';
    $content['headers'] = array();

    ob_start();

    if (is_callable($params['to']))
    {
      $output = call_user_func_array($params['to'], (array) $params);

      if (is_true($output))
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
      if (ext($params['to'], TRUE) === EXT)
      {
        $output = include $params['to'];
      }
      else
      {
        $type   = ! empty($params['type']) ? $params['type'] : mime($params['to']);
        $length = filesize($params['to']);

        $content['headers']['content-length'] = $length;
        $content['headers']['content-type'] = $type;

        readfile($params['to']);
      }
    }
    else
    {
      raise(ln('function_or_param_missing', array('name' => __FUNCTION__, 'input' => 'to')));
    }

    $content['output'] = ob_get_clean();

    if ( ! empty($output))
    {
      @list($content['status'], $content['headers']) = (array) $output;

      if ( ! empty($output['charset']))
      {
        $content['charset'] = $output['charset'];
      }

      if ( ! empty($output['type']))
      {
        $content['type'] = $output['type'];
      }
    }

    response($content);
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


  $params += $defs;


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


  trigger(__FUNCTION__, TRUE, $params);

  status($params['status'], $params['headers']);
  header('Location: ' . str_replace('&amp;', '&', $params['to']), TRUE);
  exit;
}


/**
 * Print out final content
 *
 * @param  mixed Output|Options Hash
 * @param  array Options hash
 * @return void
 */
function response($content, array $params = array())
{
  static $defs = array(
            'type'    => 'text/html',
            'charset' => CHARSET,
            'headers' => array(),
            'status'  => 200,
            'output'  => '',
            'nocache' => FALSE,
          );


  if (is_assoc($content))
  {
    $params += $content;
  }
  elseif ( ! isset($params['output']))
  {
    $params['output'] = $content;
  }

  if ( ! empty($params['text']))
  {
    $params['output'] = $params['text'];
  }


  if (empty($params['output']))
  {
    raise(ln('function_or_param_missing', array('name' => __FUNCTION__, 'input' => 'output')));
  }


  $params += $defs;

  if (empty($params['headers']))
  {
    $params['type'] = $params['type'] ?: ini_get('default_mimetype');

    if (is_mime($params['type']))
    {
      $params['headers']['content-type'] = $params['type'] . ( ! empty($params['charset']) ? "; charset=$params[charset]" : '');
      $params['headers']['content-length'] = strlen((string) $params['output']);
    }
  }

  if (is_true($params['nocache']))
  {
    $params['headers']['pragma']        = 'no-cache';
    $params['headers']['expires']       =
    $params['headers']['last-modified'] = date('D, m Y H:i:s \G\M\T', time());
    $params['headers']['cache-control'] = array(
      'no-store, no-cache, must-revalidate',
      'post-check=0, pre-check=0',
    );
  }


  trigger(__FUNCTION__, TRUE, $params);

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

      if (is_array($val))
      {
        foreach ($val as $one)
        {
          header("$key: $one", FALSE);
        }
      }
      else
      {
        header("$key: $val", TRUE);
      }
    }


    if (substr(strtoupper(PHP_SAPI), 0, 3) === 'CGI')
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

/* EOF: ./lib/tetl/router.php */
