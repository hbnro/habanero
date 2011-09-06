<?php

/**
 * Response
 */

/**
 * Dispatch a specified route
 *
 * @param  mixed  Expression|Function callback
 * @param  mixed  Function callback
 * @param  array  Options hash
 * @return void
 */
function dispatch($to = NULL, array $params = array())
{
  if (is_assoc($to))
  {
    $params = array_merge($to, $params);
  }
  elseif ( ! isset($params['to']))
  {
    $params['to'] = $to;
  }


  if (empty($params['to']))
  {
    raise(ln('function_param_missing', array('name' => __FUNCTION__, 'input' => 'to')));
  }


  $params = array_merge(array(
    'headers' => array(),
    'status'  => 200,
    'type'    => '',
    'to'      => '',
  ), $params);

  $content['output']  = '';
  $content['headers'] = $params['headers'];

  ob_start();

  if (is_callable($params['to']))
  {
    $output = apply($params['to'], (array) $params);

    if (is_true($output))
    {
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
      $type   = $params['type'] ?: mime($params['to']);
      $length = filesize($params['to']);

      $content['headers']['content-length'] = $length;
      $content['headers']['content-type'] = $type;

      readfile($params['to']);
    }
  }
  else
  {
    raise(ln('function_param_missing', array('name' => __FUNCTION__, 'input' => 'to')));
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


/**
 * Print out final content
 *
 * @param  mixed Output|Options Hash
 * @param  array Options hash
 * @return void
 */
function response($content, array $params = array())
{
  if (is_assoc($content))
  {
    $params = array_merge($content, $params);
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
    raise(ln('function_param_missing', array('name' => __FUNCTION__, 'input' => 'output')));
  }

  $params = array_merge(array(
    'type'    => 'text/html',
    'charset' => CHARSET,
    'headers' => array(),
    'status'  => 200,
    'output'  => '',
    'nocache' => FALSE,
  ), $params);

  if ($params['headers'])
  {
    $params['type'] = $params['type'] ?: ini_get('default_mimetype');

    if (is_mime($params['type']))
    {
      $params['headers']['content-type'] = $params['type'] . ($params['charset'] ? "; charset=$params[charset]" : '');
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


  status($params['status'], $params['headers']);
  echo $params['output'];
  exit;
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
  if (is_assoc($to))
  {
    $params = array_merge($to, $params);
  }
  elseif ( ! isset($params['to']))
  {
    $params['to'] = $to;
  }

  if (is_assoc($status))
  {
    $params = array_merge($status, $params);
  }
  elseif ( ! isset($params['status']))
  {
    $params['status'] = (int) $status;
  }


  if (empty($params['to']))
  {
    raise(ln('function_param_missing', array('name' => __FUNCTION__, 'input' => 'to')));
  }

  $params = array_merge(array(
    'headers' => array(),
    'locals'  => array(),
    'status'  => 200,
    'to'      => ROOT,
  ), $params);


  if ($params['to'] === 'back')
  {
    if ( ! ($params['to'] = referer()))
    {
      return FALSE;
    }
  }
  elseif ($params['locals'])
  {
    $params['to'] .= ! is_false(strrpos($params['to'], '?')) ? '&' : '?';
    $params['to'] .= http_build_query($params['locals'], NULL, '&');
  }


  status($params['status'], $params['headers']);
  header('Location: ' . str_replace('&amp;', '&', $params['to']), TRUE);
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


  if (headers_sent() OR empty($set['reasons'][$num]))
  {
    return FALSE;
  }

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
  {
    header("Status: $num {$set['reasons'][$num]}", TRUE);
  }
  else
  {
    $protocol = server('SERVER_PROTOCOL');
    header("$protocol $num {$set['reasons'][$num]}", TRUE, $num);
  }
}

/* EOF: ./lib/tetl/server/response.php */
