<?php

/**
 * Action library
 */

/**
 * Creation of internal links
 *
 * @param  mixed  Action path
 * @param  array  Options hash
 * @return string
 */
function url_for($action, array $params = array()) {
  if (is_assoc($action)) {
    $params = array_merge($action, $params);
  } elseif ( ! isset($params['action'])) {
    $params['action'] = $action;
  }


  if (empty($params['action'])) {
    raise(ln('function_param_missing', array('name' => __FUNCTION__, 'input' => 'action')));
  } elseif (is_url($params['action'])) {
    return $params['action'];
  }


  $params  = array_merge(array(
    'action'   => '',
    'anchor'   => '',
    'locals'   => array(),
    'host'     => FALSE,
    'complete' => FALSE,
  ), $params);

  $abs     = is_true($params['complete']);
  $link    = is_true($params['host']) ? server(TRUE, ROOT, $abs) : ROOT;
  $rewrite = (boolean) option('rewrite');


  ! $rewrite && $link .= INDEX;

  $anchor =
  $query  = '';

  if ( ! empty($params['action'])) {
    @list($part, $anchor) = explode('#', $params['action']);
    @list($part, $query)  = explode('?', $part);

    $link .= ($rewrite ? '' : '/') . ltrim($part, '/');
  }

  ! preg_match('/(?:\/|\.\w+)$/', $link) && $link .= '/';


  if ( ! empty($params['locals'])) {
    $test = array();
    $hash = uniqid('--query-prefix');

    parse_str($query, $test);

    $query = http_build_query(array_merge($test, $params['locals']), $hash, '&amp;');
    $query = preg_replace("/{$hash}\d+=/", '', $query);
  }

  $params['anchor'] && $anchor = $params['anchor'];

  $link .= $query ? "?$query" : '';
  $link .= $anchor ? "#$anchor" : '';

  return $link;
}


/**
 * Prepare link automatically
 *
 * @param  string Link|Email|Path
 * @return string
 */
function pre_url($text) {
  $text = str_replace('mailto:', '', $text);

  if (preg_match('/[?#]/', $text) OR (substr($text, 0, 2) == '//')) {
    return $text;
  }


  if (is_email($text)) {
    $text = 'mailto:' . rawurlencode($text);
  } elseif (substr($text, 0, 1) === '/') {
    $text = url_for($text, array(
      'complete' => TRUE,
      'host' => TRUE,
    ));
  } elseif (substr($text, 0, 2) == './') {
    $text = server(TRUE, ROOT . substr($text, 2), TRUE);
  } elseif ( ! preg_match('/^[a-z]{2,7}:\/\//', $text)) {
    $text = "http://$text";
  }

  return $text;
}


/**
 * HTML tag link builder
 *
 * @param  mixed  Link text|Options hash|Path
 * @param  mixed  Options hash|Path|Function callback
 * @param  mixed  Attributes|Function callback
 * @return string
 */
function link_to($text, $url = NULL, $args = array()) {
  $attrs  =
  $params = array();

  if (is_assoc($text)) {
    $params = $text;
  } elseif (is_assoc($url)) {
    $params = array_merge($url, $params);
    $params['text'] = (string) $text;
  } elseif (is_closure($url)) {
    $params['action'] = $text;

    $args = $url;
  } elseif ( ! isset($params['text'])) {
    $params['text'] = $text;
  }

  if (is_assoc($url)) {
    $attrs  = $args;
    $params = array_merge($params, $url);
  } elseif ( ! isset($params['action']) && is_string($url)) {
    $params['action'] = $url;
  }


  if (empty($params['text'])) {
    raise(ln('function_param_missing', array('name' => __FUNCTION__, 'input' => 'text')));
  }


  if (is_closure($args)) {
    ob_start() && $args();

    $params['text'] = trim(ob_get_clean());
  } else {
    $attrs = array_merge($attrs, (array) $args);
  }


  $params = array_merge(array(
    'action'  => slug($params['text']),
    'method'  => GET,
    'confirm' => FALSE,
    'remote'  => FALSE,
    'params'  => FALSE,
    'type'    => FALSE,
  ), $params);

  return tag('a', array_merge(array(
    'rel' => $params['method'] <> GET ? 'nofollow' : FALSE,
    'href' => $params['action'],
    'data-method' => $params['method'] <> GET ? strtolower($params['method']) : FALSE,
    'data-remote' => is_true($params['remote']) ? 'true' : FALSE,
    'data-params' => $params['params'] ? json_encode($params['params']) : FALSE,
    'data-confirm' => $params['confirm'] ?: FALSE,
    'data-type' => $params['type'] ?: FALSE,
  ), $attrs), $params['text']);
}


/**
 * HTML tag email link builder
 *
 * @param  mixed  Email address|Options hash
 * @param  mixed  Link text|Options hash
 * @param  array  Options hash
 * @return string
 */
function mail_to($address, $text = NULL, array $args = array()) {
  $vars   =
  $params = array();

  if (is_assoc($address)) {
    $params = $address;
  } elseif ( ! isset($params['address'])) {
    $params['address'] = $address;
  }

  if (is_assoc($text)) {
    $params = array_merge($text, $params);
  } elseif ( ! isset($params['text'])) {
    $params['text'] = is_string($address) ? $address : $text;
  }


  if (empty($params['text'])) {
    raise(ln('function_param_missing', array('name' => __FUNCTION__, 'input' => 'text')));
  }


  $params = array_merge(array(
    'address'     => '',
    'encode'      => FALSE,
    'replace_at'  => '&#64;',
    'replace_dot' => '&#46;',
    'subject'     => '',
    'body'        => '',
    'bcc'         => '',
    'cc'          => '',
  ), $params);

  foreach (array('subject', 'body', 'bcc', 'cc') as $key) {
    if ( ! empty($params[$key])) {
      $vars[$key] = $params[$key];
    }
  }

  $params['text'] = $params['text'] ?: $params['address'];
  $params['text'] = str_replace('@', $params['replace_at'], $params['text']);
  $params['text'] = str_replace('.', $params['replace_dot'], $params['text']);

  $vars = $vars ? '?' . http_build_query($vars) : '';

  if ($params['encode'] === 'hex') {
    $test   = '';
    $length = strlen($params['address']);

    for ($i = 0; $i < $length; $i += 1) {
      $char  = substr($params['address'], $i, 1);
      $test .= ! in_array($char, array('@', '.')) ? '%' . base_convert(ord($char), 10, 16) : $char;
    }

    $params['address'] = $test;
  } elseif ($params['encode'] === 'javascript') {
    return tag('script', array(
      'type' => 'text/javascript',
    ), sprintf('document.write("%s")', preg_replace_callback('/./', function ($match) {
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
function path_to($path = '.', $host = FALSE) {
  static $root = NULL;


  if (is_null($root)) {// only apply on real root-based files
    $root = realpath($_SERVER['DOCUMENT_ROOT']);
  }


  if ($path = APP_PATH.DS.'static'.DS.$path) {
    if ($root <> '/') {
      $path = str_replace($root, '', $path);
    }#

    $path = url_for(str_replace('/./', '/', strtr($path, '\\', '/')));
    $path = is_true($host) ? server(TRUE, $path, TRUE) : $path;
  }

  return $path;
}


/**
 * HTML tag action button builder
 *
 * @param  mixed  Button text|Path|Options hash
 * @param  mixed  Path|Options hash
 * @param  array  Option hash
 * @return string
 */
function button_to($name, $url = NULL, array $args = array()) {
  $params = array();

  if (is_assoc($name)) {
    $params = $name;
  } elseif (is_assoc($url)) {
    $params['action'] = (string) $name;
  } elseif ( ! isset($params['text'])) {
    $params['text'] = $name;
  }

  if (is_string($name) && is_assoc($url)) {
    $params = array_merge($url, $params);
  } elseif ( ! isset($params['action'])) {
    $params['action'] = $url;
  }


  if (empty($params['text'])) {
    raise(ln('function_param_missing', array('name' => __FUNCTION__, 'input' => 'text')));
  }


  $params = array_merge(array(
    'type'         => FALSE,
    'action'       => slug($params['text']),
    'method'       => POST,
    'remote'       => FALSE,
    'params'       => FALSE,
    'confirm'      => FALSE,
    'disabled'     => FALSE,
    'disable_with' => '',
  ), $params);

  $button = tag('input', array_merge(array(
    'type' => 'submit',
    'value' => $params['text'],
    'disabled' => is_true($params['disabled']),
    'data-disable-with' => $params['disable_with'] ?: FALSE,
  ), $args));


  $extra = '';

  if ($params['method'] <> POST) {
    $extra = tag('input', array(
      'type' => 'hidden',
      'name' => '_method',
      'value' => strtolower($params['method']),
    ));
  }

  $extra .= tag('input', array(
    'type' => 'hidden',
    'name' => '_token',
    'value' => option('csrf_token'),
  ));


  return tag('form', array(
    'class' => 'button_to',
    'action' => $params['action'],
    'method' => 'post',
    'data-type' => $params['type'] ?: FALSE,
    'data-confirm' => $params['confirm'] ?: FALSE,
    'data-remote' => is_true($params['remote']) ? 'true' : FALSE,
    'data-params' => $params['params'] ? http_build_query($params['params']) : FALSE,
  ), "<div>$extra$button</div>");
}


/**
 * Handle flashable redirections
 *
 * @param     mixed  Path|Options hash
 * @param     array  Options hash
 * @staticvar array  Flash keys
 * @return    string
 */
function redirect_to($path, array $params = array()) {
  static $allow = array('success', 'notice', 'alert', 'error', 'info');


  if (is_assoc($path)) {
    $params = $path;
    $path   = '';
  } else {// TODO: just works with this?
    $params['to'] = url_for::apply($path);
  }


  if (empty($params['to'])) {
    raise(ln('function_param_missing', array('name' => __FUNCTION__, 'input' => 'to')));
  }


  foreach ($allow as $type) {
    if (isset($params[$type])) {
      flash($type, $params[$type]);
      unset($params[$type]);
    }
  }

  redirect($params);
}

/* EOF: ./library/www/actions.php */
