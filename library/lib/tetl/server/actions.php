<?php

/**
 * Hyperlinks action library
 */

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
  static $defs = array(
            'complete' => FALSE,
            'locals'   => array(),
            'host'     => FALSE,
            'to'       => '',
          );


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


  $params += $defs;

  $abs     = is_true($params['complete']);
  $link    = is_true($params['host']) ? server(TRUE, ROOT, $abs) : ROOT;
  $rewrite = (boolean) option('rewrite');

  if ( ! $rewrite)
  {
    $link .= INDEX . (option('query') ? '?' : '');
  }

  $anchor =
  $query  = '';

  if ( ! empty($params['to']))
  {
    @list($part, $anchor) = explode('#', $params['to']);
    @list($part, $query)  = explode('?', $part);

    $part = ltrim($part, '/');//FIX
    $part && $link .= '/' . $part;
  }


  if ($rewrite && ! preg_match('/(?:\/|\.\w+)$/', $link))
  {
    $link .= option('suffix');
  }


  if ( ! empty($params['locals']))
  {
    $hash  = uniqid('--query-prefix');
    $test  = http_build_query($params['locals'], $hash, '&amp;');
    $test  = preg_replace("/{$hash}\d+=/", '', $test);
    $link .= (option('query') ? '&' : '?') . $test;
  }

  $link .= $query ? "&$query" : '';
  $link .= $anchor ? "#$anchor" : '';

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
  elseif (substr($text, 0, 1) === '/')
  {
    $text = link_to($text, array(
      'complete' => TRUE,
      'host' => TRUE,
    ));
  }
  elseif (substr($text, 0, 2) == './')
  {
    $text = server(TRUE, ROOT . substr($text, 2));
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

/* EOF: ./lib/tetl/server/actions.php */
