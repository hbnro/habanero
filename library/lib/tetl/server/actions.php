<?php

/**
 * Hyperlinks action library
 */

/**
 * Creation of internal links
 *
 * @param  mixed  Action path
 * @param  array  Options hash
 * @return string
 */
function url_for($action, array $params = array())
{
  if (is_array($action))
  {
    $params += $action;
  }
  elseif ( ! isset($params['action']))
  {
    $params['action'] = $action;
  }


  if (empty($params['action']))
  {
    raise(ln('function_param_missing', array('name' => __FUNCTION__, 'input' => 'action')));
  }


  $params  = array_merge(array(
    'action'   => '',
    'locals'   => array(),
    'host'     => FALSE,
    'complete' => FALSE,
  ), $params);

  $abs     = is_true($params['complete']);
  $link    = is_true($params['host']) ? server(TRUE, ROOT, $abs) : ROOT;
  $rewrite = (boolean) option('rewrite');

  if ( ! $rewrite)
  {
    $link .= INDEX . (option('query') ? '?' : '');
  }

  $anchor =
  $query  = '';

  if ( ! empty($params['action']))
  {
    @list($part, $anchor) = explode('#', $params['action']);
    @list($part, $query)  = explode('?', $part);

    $part = ltrim($part, '/');
    $part && $link .= $part;
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
    $text = url_for($text, array(
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
 * Resolve server-based urls
 *
 * @param     string  File or directory
 * @param     boolean Prefix host?
 * @staticvar string  Root
 * @return    string
 */
function link_to($text, $url = NULL, $args = array())
{
  $attrs  =
  $params = array();

  if (is_assoc($text))
  {
    $params += $text;
  }
  elseif (is_assoc($url))
  {
    $params['action'] = (string) $text;
  }
  elseif (is_closure($url))
  {
    $params['action'] = $text;

    $args = $url;
  }
  elseif ( ! isset($params['text']))
  {
    $params['text'] = $text;
  }

  if (is_assoc($url))
  {
    $attrs += $url;
  }
  elseif ( ! isset($params['action']))
  {
    $params['action'] = $url;
  }


  if (is_closure($args))
  {
    ob_start() && $args();

    $params['text'] = trim(ob_get_clean());
  }
  else
  {
    $attrs = array_merge($attrs, (array) $args);
  }


  $params = array_merge(array(
    'action' => slug($params['text']),
    'method' => GET,
    'confirm' => FALSE,
  ), $params);

  return tag('a', array_merge(array(
    'href' => url_for($params),
    'data-method' => $params['method'] <> GET ? strtolower($params['method']) : FALSE,
    'data-confirm' => $params['confirm'] ?: FALSE,
  ), $attrs), $params['text']);
}


/**
 * Resolve server-based urls
 *
 * @param     string  File or directory
 * @param     boolean Prefix host?
 * @staticvar string  Root
 * @return    string
 */
function mail_to($address, $text = NULL, array $args = array())
{
  $vars   =
  $params = array();

  if (is_array($address))
  {
    $params += $address;
  }
  elseif ( ! isset($params['address']))
  {
    $params['address'] = $address;
  }

  if (is_array($text))
  {
    $params += $text;
  }
  elseif ( ! isset($params['text']))
  {
    $params['text'] = $text;
  }


  $params = array_merge(array(
    'text'        => '',
    'address'     => '',
    'encode'      => FALSE,
    'replace_at'  => '&#64;',
    'replace_dot' => '&#46;',
    'subject'     => '',
    'body'        => '',
    'bcc'         => '',
    'cc'          => '',
  ), $params);

  foreach (array('subject', 'body', 'bcc', 'cc') as $key)
  {
    if ( ! empty($params[$key]))
    {
      $vars[$key] = $params[$key];
    }
  }

  $params['text'] = $params['text'] ?: $params['address'];
  $params['text'] = str_replace('@', $params['replace_at'], $params['text']);
  $params['text'] = str_replace('.', $params['replace_dot'], $params['text']);

  $vars = $vars ? '?' . http_build_query($vars) : '';

  if ($params['encode'] === 'hex')
  {
    $test   = '';
    $length = strlen($params['address']);

    for ($i = 0; $i < $length; $i += 1)
    {
      $char  = substr($params['address'], $i, 1);
      $test .= ! in_array($char, array('@', '.')) ? '%' . base_convert(ord($char), 10, 16) : $char;
    }

    $params['address'] = $test;
  }
  elseif ($params['encode'] === 'javascript')
  {
    return tag('script', array(
      'type' => 'text/javascript',
    ), sprintf('document.write("%s")', preg_replace_callback('/./', function($match)
    {
      return '\x' . base_convert(ord($match[0]), 10, 16);
    }, tag('a', array(
      'href' => "mailto:$params[address]$vars"
    ), $params['text']))));
  }

  return tag('a', array_merge(array(
    'href' => "mailto:$params[address]$vars",
  ), $args), $params['text']);
}


/**
 * Resolve server-based urls
 *
 * @param     string  File or directory
 * @param     boolean Prefix host?
 * @staticvar string  Root
 * @return    string
 */
function path_to($path = '.', $host = FALSE)
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
 * Resolve server-based urls
 *
 * @param     string  File or directory
 * @param     boolean Prefix host?
 * @staticvar string  Root
 * @return    string
 */
function button_to($name, $url = NULL, array $args = array())
{
  $params = array();

  if (is_array($url))
  {
    $params += $url;
  }
  elseif ( ! isset($params['action']))
  {
    $params['action'] = $url;
  }


  $params = array_merge(array(
    'action'       => slug($name),
    'method'       => POST,
    'remote'       => FALSE,
    'confirm'      => FALSE,
    'disabled'     => FALSE,
    'disable_with' => '',
  ), $params);

  $button = tag('input', array_merge(array(
    'type' => 'submit',
    'value' => $name,
    'disabled' => is_true($params['disabled']),
    'data-confirm' => $params['confirm'] ?: FALSE,
    'data-disabled' => $params['disable_with'] ?: FALSE,
  ), $args));


  $extra = '';

  if ($params['method'] <> POST)
  {
    $extra = tag('input', array(
      'type' => 'hidden',
      'name' => '_method',
      'value' => strtolower($params['method']),
    ));
  }


  return tag('form', array(
    'class' => 'button_to',
    'action' => url_for($params['action']),
    'method' => 'post',
    'data-remote' => is_true($params['remote']) ? 'true' : FALSE,
  ), "<div>$extra$button</div>");
}

/* EOF: ./lib/tetl/server/actions.php */
