<?php
// TODO: rearrange for clarity
/**
 * Basic routing/request functions
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
 * Get variable access
 *
 * @param  string Key or name
 * @param  mixed  Default value
 * @return mixed
 */
function get($key, $or = FALSE)
{
  return value($_GET, $key, $or);
}


/**
 * Post variable access
 *
 * @param  string Key or name
 * @param  mixed  Default value
 * @return mixed
 */
function post($key, $or = FALSE)
{
  return value($_POST, $key, $or);
}


/**
 * Upload variable access
 *
 * @param  string Key or name
 * @return mixed
 */
function upload($key)
{
  return value($_FILES, $key);
}


/**
 * Common client IP
 *
 * @param  string Default value
 * @return string
 */
function remote($or = FALSE)
{
  return server('HTTP_X_FORWARDED_FOR', server('HTTP_CLIENT_IP', server('REMOTE_ADDR', $or)));
}


/**
 * Client address
 *
 * @return string
 */
function addr()
{
  return is_callable('gethostbyaddr') ? gethostbyaddr(remote()) : remote();
}


/**
 * Remote port
 *
 * @return integer
 */
function port()
{
  return (int) server('REMOTE_PORT');
}


/**
 * HTTP referer
 *
 * @param  string Valor por defecto
 * @return mixed
 */
function referer($or = FALSE)
{
  return server('HTTP_REFERER', $or);
}


/**
 * User agent
 *
 * @return mixed
 */
function agent()
{
  return server('HTTP_USER_AGENT');
}


/**
 * HTTP method
 *
 * @return string
 */
function method()
{
  return server('REQUEST_METHOD');
}


/**
 * Server variable access
 *
 * @param  string  Key or name
 * @param  mixed   Default value
 * @param  boolean Use full scheme?
 * @return mixed
 */
function server($key = '', $default = FALSE, $complete = FALSE)
{
  global $_SERVER;
  
  if (func_num_args() == 0)
  {
    $test = explode('.', $_SERVER['SERVER_NAME']);


    if ( ! empty($test[0]) && ($test[0] === 'www'))
    {
      array_shift($test);
    }

    return join('.', $test);
  }
  elseif (is_true($key))
  {
    $host = '';

    if (is_true($complete))
    {
      $pre   = explode('/', $_SERVER['SERVER_PROTOCOL']);

      $host .= strtolower(array_shift($pre));
      $host .= ! empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 's' : '';
      $host .= ':';//FIX
    }

    $host .= '//' . strtolower($_SERVER['HTTP_HOST']);
    $host .= (int) $_SERVER['SERVER_PORT'] !== 80 ? ":$_SERVER[SERVER_PORT]" : '';
    $host .= ! is_false($default) ? $default : '';

    return $host;
  }
  elseif ( ! empty($_SERVER[$key]))
  {
    return $_SERVER[$key];
  }
  elseif ($test = getenv($key))
  {
    return $test;
  }

  return $default;
}


/**
 * Resolve server-based urls
 *
 * @param     string  File or directory
 * @param     boolean Prefix host?
 * @staticvar string  Root
 * @return    string
 */
function url_to($path = '.', $host = FALSE)
{
  static $root = NULL;


  if (is_null($root))
  {
    $root = realpath($_SERVER['DOCUMENT_ROOT']);
  }


  if ($path = realpath($path))
  {
    if ($root <> '/')
    {
      $path = str_replace($root, '', $path);
    }

    $path = strtr($path, '\\', '/');
    $path = is_true($host) ? server(TRUE, $path) : $path;
  }

  return $path;
}


/**
 * Creation of internal links
 *
 * @param  mixed  Path
 * @param  array  Options hash
 * @return string
 */
function link_to($route, array $params = array())
{
  if (is_array($route))
  {
    $params += $route;
  }
  elseif ( ! isset($params['to']))
  {
    $params['to'] = $route;
  }
  
  
  if (empty($params['to']))
  {
    raise(ln('function_or_param_missing', array('name' => __FUNCTION__, 'input' => 'to')));
  }
  elseif (is_closure($params['to']))
  {
    return filter(__FUNCTION__, $params['to']);
  }

  
  $params = filter(__FUNCTION__, extend(array(
    'complete' => FALSE,
    'locals'   => array(),
    'host'     => FALSE,
    'to'       => '.',
  ), $params), TRUE);

  if (is_url($params['to']) OR preg_match('/^[?#.]/', $params['to']))
  {
    $link = $params['to'] === '.' ? ROOT : $params['to'];
  }
  else
  {
    $abs     = is_true($params['complete']);
    $link    = is_true($params['host']) ? server(TRUE, ROOT, $abs) : ROOT;
    $rewrite = (boolean) option('rewrite');

    if ( ! $rewrite)
    {
      $link .= INDEX . (option('query') ? '?' : '') . '/';
    }

    if ( ! empty($params['to']))
    {
      $link .= $params['to'];
    }


    $regex  = '/^(?<!:';
    $regex .= strpos($link, server()) === 2 ? '|^' : '';
    $regex .= ')\/+/';

    $link = preg_replace($regex, '/', $link);

    if ($rewrite && ! preg_match('/(?:\/|\.\w+)$/', $link))
    {
      $link .= option('suffix');
    }
  }


  if ( ! empty($params['locals']))
  {
    $hash  = uniqid('--query-prefix');
    $test  = http_build_query($params['locals'], $hash, '&amp;');
    $test  = preg_replace("/{$hash}\d+=/", '', $test);
    $link .= (option('query') ? '&' : '?') . $test;
  }

  return $link;
}


/**
 * Prepare link automatically
 *
 * @param  string Link|Email|Path
 * @return string
 */
function pre_url($text)
{
  $text = str_replace('mailto:', '', $text);

  if (preg_match('/[?#]/', $text) OR (substr($text, 0, 2) == '//'))
  {
    return $text;
  }


  if (is_email($text))
  {
    $text = 'mailto:' . rawurlencode($text);
  }
  elseif (substr($text, 0, 1) == '/')
  {
    $text = link_to($text, array(
      'complete' => TRUE,
      'host' => TRUE,
    ));
  }
  elseif (substr($text, 0, 2) == './')
  {
    $text = server(TRUE, ROOT) . substr($text, 2);
  }
  elseif ( ! preg_match('/^[a-z]{2,7}:\/\//', $text))
  {
    $text = "http://$text";
  }

  return $text;
}


/**
 * Make other requests
 *
 * @link   http://www.php.net/manual/en/function.fsockopen.php#39868
 * @param  string Request location
 * @param  string Request params
 * @param  string Upload files
 * @param  mixed  GET|PUT|POST|DELETE
 * @return mixed
 */
function submit_to($url, array $args = array(), array $files = array(), $method = POST)
{
  if ( ! is_callable('fsockopen'))
  {
    raise(ln('extension_missing', array('name' => 'Sockets')));
  }
  elseif ( ! is_url($url))
  {
    return FALSE;
  }
  
  

  $test  = @parse_url($url);

  $path  = ! empty($test['path']) ? $test['path'] : '/';
  $path .= ! empty($test['query']) ? '?' . $test['query'] : '';
  $port  = ! empty($test['port']) ? $test['port'] : 80;

  $resource = fsockopen($test['host'], $test['scheme'] !== 'https' ? $port : 433);

  if ( ! is_resource($resource))
  {
    return FALSE;
  }

  $bound  = uniqid('--post-boundary');
  $output = "--$bound";

  if ( ! empty($args))
  {
    foreach ($args as $name => $value)
    {
      $output .= "\r\nContent-Disposition: form-data; name=\"" . slug($name) . '"';
      $output .= "\r\n\r\n$value\r\n--$bound";
    }
  }

  // upload
  if ( ! empty($files))
  {
    foreach ((array) $files as $name => $set)
    {
      if ( ! is_file($set[0]) && ! is_url($set[0]))
      {
        continue;
      }

      $data = read($set[0]);
      $name = preg_replace('/[^\w.]/', '', is_num($name) ? $set[0] : $name);

      $output .= "\r\nContent-Disposition: form-data; name=\"" . $name . '"; filename="' . $set[0] . '"';
      $output .= "\r\nContent-Type: " . $set[1];
      $output .= "\r\n\r\n$data\r\n--$bound";
    }
  }

  $output .= "--\r\n\r\n";

  fputs($resource, "$method $path HTTP/1.0\r\n");

  fputs($resource, "Content-Type: multipart/form-data; boundary=$bound\r\n");
  fputs($resource, 'Content-Length: ' . strlen($output) . "\r\n");
  fputs($resource, "Connection: close\r\n\r\n");
  fputs($resource, "$output\r\n");


  $output = '';
  
  while( ! feof($resource))
  {
    $output .= fgets($resource, 4096);
  }
  
  return $output;
}


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
  elseif (is_closure($params['match']))
  {
    return filter(__FUNCTION__, $params['match']);
  }


  $params['match'] = trim($params['match']);

  if (is_false(strpos($params['match'], ' ')))
  {
    $params['match'] = 'GET ' . $params['match'];
  }

  $params = filter(__FUNCTION__, extend(array(
    'constraints' => array(),
    'defaults'    => array(),
    'route'       => $params['match'],
    'to'          => 'raise',
  ), $params), TRUE);


  
  $expr = "^$params[route]$";
  $test = method() . ' ' . URI;
  
  $params['matches'] = match($expr, $test, (array) $params['constraints']);

  if ( ! empty($params['matches']))
  {
    if ($params['to'] === '.')
    {
      $params['to'] = link_to('.');
    }
    
    
    if (empty($params['to']) OR
     ! (is_closure($params['to']) OR
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
 * @param  mixed Key or name|Hash
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

/* EOF: ./core/request.php */