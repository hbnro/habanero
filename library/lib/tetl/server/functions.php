<?php

/**
 * Basic server functions
 */

/**
 * PUT variable access
 */
function put()
{
  if ( ! is_put())
  {
    return FALSE;
  }
  
  
  $out = (string) @file_get_contents('php://input');
  
  if (server('HTTP_CONTENT_TYPE') === 'application/x-www-form-urlencoded')
  {
    parse_str($out, $out);
  }
  return $out;
}


/**
 * GET variable access
 *
 * @param  string Identifier
 * @param  mixed  Default value
 * @return mixed
 */
function get($key, $or = FALSE)
{
  return value($_GET, $key, $or);
}


/**
 * POST variable access
 *
 * @param  string Identifier
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
 * @param  string Identifier
 * @return mixed
 */
function upload($key)
{
  return value($_FILES, $key, array());
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
 * Server variable access
 *
 * @param  string  Identifier
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

/* EOF: ./lib/tetl/server/functions.php */