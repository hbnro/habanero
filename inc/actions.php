<?php

// urls
function url_for()
{
  $path = array();
  $params = array();
  $arguments = func_get_args();
  $method = array_shift($arguments);

  foreach ($arguments as $one) {
    is_array($one) ? $params += $one : $path []= $one;
  }

  $url = \Broil\Routing::path($method, $params);

  array_unshift($path, $url);

  return join('/', $path);
}

function root_url($path = '')
{
  is_file($path) && $path = strtr(str_replace(APP_PATH, '', $path), '\\/', '//');

  return \Broil\Helpers::build(trim($path, '/'), array('static' => TRUE));
}

// links
function link_to($text, $url = '', $args = array())
{
  $attrs  =
  $params = array();

  if ($args instanceof \Closure) {
    $params['text'] = $args;
  } else {
    $attrs = array_merge($attrs, (array) $args);
  }

  if (is_array($url)) {
    $params = array_merge($params, $url);
  } elseif ($url instanceof \Closure) {
    $params['text'] = $url;
    $params['action'] = $text;
  } else {
    $params['action'] = (string) $url;
  }

  if (is_array($text)) {
    $params = array_merge($params, $text);
  } else {
    $params['text'] = $text;
  }


  $props = array(
    'text'    => '',
    'action'  => '',
    'method'  => 'GET',
    'confirm' => FALSE,
    'remote'  => FALSE,
    'params'  => FALSE,
    'type'    => FALSE,
  );

  $params = array_merge($props, $params, array_intersect_key($args, $props));
  $attrs  = array_diff_key($attrs, array_intersect_key($attrs, $props));

  if ($params['text'] instanceof \Closure) {
    ob_start() && call_user_func($params['text']);
    $params['text'] = trim(ob_get_clean());
  }

  return tag('a', $params['action'], $params['text'] ?: $params['action'], array_merge(array(
    'rel' => $params['method'] <> 'GET' ? 'nofollow' : FALSE,
    'data-method' => $params['method'] <> 'GET' ? strtolower($params['method']) : FALSE,
    'data-remote' => $params['remote'] ? 'true' : FALSE,
    'data-params' => $params['params'] ? json_encode($params['params']) : FALSE,
    'data-confirm' => $params['confirm'] ?: FALSE,
    'data-type' => $params['type'] ?: FALSE,
  ), $attrs));
}

function button_to($name, $url = '', array $args = array())
{
  $params = array();

  if (is_array($name)) {
    $params = $name;
  } elseif (is_array($url)) {
    $params['action'] = (string) $name;
  } elseif ( ! isset($params['text'])) {
    $params['text'] = $name;
  }

  if (is_string($name) && is_array($url)) {
    $params = array_merge($url, $params);
  } elseif ( ! isset($params['action'])) {
    $params['action'] = $url;
  }


  $props = array(
    'type'         => FALSE,
    'action'       => '',
    'method'       => 'POST',
    'remote'       => FALSE,
    'params'       => FALSE,
    'confirm'      => FALSE,
    'disabled'     => FALSE,
    'disable_with' => '',
  );

  $params = array_merge($props, $params, array_intersect_key($args, $props));
  $args = array_diff_key($args, array_intersect_key($args, $props));

  $button = tag('input', array_merge(array(
    'type' => 'submit',
    'value' => $params['text'],
    'disabled' => $params['disabled'],
    'data-disable-with' => $params['disable_with'] ?: FALSE,
  ), $args));

  $extra = '';

  if ($params['method'] <> 'POST') {
    $extra = tag('input', array(
      'type' => 'hidden',
      'name' => '_method',
      'value' => strtolower($params['method']),
    ));
  }

  $extra .= tag('input', array(
    'type' => 'hidden',
    'name' => '_token',
    'value' => \Labourer\Web\Session::token(),
  ));

  return tag('form', array(
    'class' => 'button-to',
    'action' => $params['action'],
    'method' => 'post',
    'data-type' => $params['type'] ?: FALSE,
    'data-confirm' => $params['confirm'] ?: FALSE,
    'data-remote' => $params['remote'] ? 'true' : FALSE,
    'data-params' => $params['params'] ? http_build_query($params['params']) : FALSE,
  ), "<div>$extra$button</div>");
}

// generators
function paginate_to($url, $mapper, $current = 0, $limit = 10)
{
  // TODO: allow more params
  $pg = \Staple\Paginate::build();

  if (strpos($url, '?') !== FALSE) {
    list($url, $vars) = explode('?', $url);

    $pg->set('link_href', "?$vars");
  }

  $pg->set('count_page', $limit);
  $pg->set('link_root', $url);

  $from = $pg->offset($mapper->count(), $current);

  return $pg->bind($mapper->offset($from)->limit($limit));
}

function redirect_to($path, array $params = array())
{
  static $allow = array('success', 'notice', 'alert', 'error', 'info');

  if (is_array($path)) {
    $params = $path;
    $path   = '';
  } else {// TODO: just works with this?
    $params['to'] = url_for($path);
  }

  foreach ($allow as $type) {
    if (isset($params[$type])) {
      flash($type, $params[$type]);
      unset($params[$type]);
    }
  }

  return redirect($params);
}

function cache_for($id, $ttl, \Closure $lambda)
{
  \Cashier\Base::block($id, $ttl < 0 ? time() : (int) $ttl, $lambda);
}
