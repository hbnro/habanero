<?php

// TODO: group functions by task

function run(\Closure $lambda)
{
  echo \Sauce\Base::initialize($lambda);
  \Sauce\Logger::log(sprintf(' => %s', microtime(TRUE) - BEGIN));
}

function option($get, $or = FALSE)
{
  return value(\Sauce\Config::all(), $get, $or);
}

function value($from, $that, $or = FALSE)
{
  return \Staple\Helpers::fetch($from, $that, $or);
}

function config($set = NULL, $value = NULL)
{
  if (func_num_args() === 0) {
    return \Sauce\Config::all();
  } elseif (func_num_args() === 2) {
    \Sauce\Config::set($set, $value);
  } else {
    $tmp = array();

    if ($set instanceof \Closure) {
      $tmp = (object) $tmp;
      $set($tmp);
    } elseif (is_file($set)) {
      $tmp = call_user_func(function () {
          include func_get_arg(0);
          return get_defined_vars();
        }, $set);

      $tmp = isset($tmp['config']) ? $tmp['config'] : $tmp;
    }

    \Sauce\Config::add((array) $tmp);
  }
}

function params($key = NULL, $default = FALSE)
{
  static $set = array();

  if ( ! func_num_args()) {
    return $set;
  } elseif (is_array($key)) {
    $set = array_merge($set, $key);
  } else {
    return value($set, $key, $default);
  }
}

function ln($input, array $params = array())
{
  return \Locale\Base::digest($input, $params);
}

function get($path, $to, array $params = array())
{
  \Broil\Routing::add('GET', $path, $to, $params);
}

function put($path, $to, array $params = array())
{
  \Broil\Routing::add('PUT', $path, $to, $params);
}

function post($path, $to, array $params = array())
{
  \Broil\Routing::add('POST', $path, $to, $params);
}

function patch($path, $to, array $params = array())
{
  \Broil\Routing::add('PATCH', $path, $to, $params);
}

function delete($path, $to, array $params = array())
{
  \Broil\Routing::add('DELETE', $path, $to, $params);
}

function root($to, array $params = array())
{
  \Broil\Routing::add('GET', '/', $to, $params);
}

function mount(Closure $block, array $params = array())
{
  \Broil\Routing::mount($block, $params);
}

function method()
{
  return \Postman\Request::method();
}

function server()
{
  return call_user_func_array('\\Postman\\Request::env', func_get_args());
}

function redirect($url = ROOT)
{
  return call_user_func_array(array(\Sauce\Base::$response, 'redirect'), func_get_args());
}

function session($key, $value = FALSE)
{
  if (func_num_args() === 1) {
    return \Labourer\Web\Session::get($key);
  } else {
    return \Labourer\Web\Session::set($key, $value);
  }
}

function flash()
{
  return call_user_func_array('\\Labourer\\Web\\Session::flash', func_get_args());
}

function path()
{
  return call_user_func_array('\\IO\\Helpers::join', func_get_args());
}

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

  $params = array_merge(array(
    'text'    => '',
    'action'  => '',
    'method'  => 'GET',
    'confirm' => FALSE,
    'remote'  => FALSE,
    'params'  => FALSE,
    'type'    => FALSE,
  ), $params);


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


  $params = array_merge(array(
    'type'         => FALSE,
    'action'       => '',
    'method'       => 'POST',
    'remote'       => FALSE,
    'params'       => FALSE,
    'confirm'      => FALSE,
    'disabled'     => FALSE,
    'disable_with' => '',
  ), $params);

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


function after_body()
{
  return \Sauce\App\Assets::after();
}

function before_body()
{
  return \Sauce\App\Assets::before();
}

function javascript_for($name)
{
  \Sauce\App\Assets::inline(join("\n", \Sauce\App\Assets::build($name, 'scripts_dir')), 'body', TRUE);
}

function prepend_js($test, $to = 'head')
{
  \Sauce\App\Assets::prepend($test, $to);
}

function append_js($test, $to = 'head')
{
  \Sauce\App\Assets::append($test, $to);
}

function stylesheet_for($name)
{
  \Sauce\App\Assets::inline(join("\n", \Sauce\App\Assets::build($name, 'styles_dir')), 'head', TRUE);
}

function prepend_css($test, $to = 'head')
{
  \Sauce\App\Assets::prepend($test, $to);
}

function append_css($test, $to = 'head')
{
  \Sauce\App\Assets::append($test, $to);
}

function csrf_meta_tag()
{
  return \Labourer\Web\Html::meta('csrf-token', \Labourer\Web\Session::token());
}

function cache_for($id, $ttl, \Closure $lambda)
{
  \Cashier\Base::block($id, $ttl < 0 ? time() : (int) $ttl, $lambda);
}

function tag_for($src)
{
  return \Sauce\App\Assets::tag_for($src);
}

function root_url($path = '')
{
  is_file($path) && $path = strtr(str_replace(APP_PATH, '', $path), '\\/', '//');
  return \Broil\Helpers::build(trim($path, '/'), array('static' => TRUE));
}

function asset_url($path)
{
  return \Sauce\App\Assets::asset_url($path);
}

function image_tag($src, $alt = NULL, array $attrs = array())
{
  if (is_array($alt)) {
    $attrs = $alt;
    $alt   = $src;
  }

  if ( ! $alt OR ($alt === $src)) {
    $ext = \IO\File::ext($src, TRUE);
    $alt = titlecase(basename($src, $ext));
  }

  $attrs['alt'] = $attrs['title'] = $alt;


  try {
    $img = \Tailor\Helpers::image($src);

    $attrs['width'] = $img['dims'][0];
    $attrs['height'] = $img['dims'][1];

    $attrs['src'] = asset_url($src);
  } catch (\Exception $e) {
    $attrs['src'] = $src;
  }

  return \Labourer\Web\Html::tag('img', $attrs);
}

function partial($path, array $vars = array())
{
  return \Tailor\Base::render(\Tailor\Base::partial($path), $vars);
}

function write($file, $content, $append = FALSE)
{
  return \IO\File::write($file, $content, $append);
}

function read($file)
{
  return \IO\File::read($file);
}

function tag($name)
{
  return call_user_func_array("\\Labourer\\Web\\Html::$name", array_slice(func_get_args(), 1));
}

function e($text)
{
  return \Labourer\Web\Html::ents($text, TRUE);
}

function plain($text)
{
  return \Labourer\Web\Text::plain(\Labourer\Web\Html::unents($text));
}

function plural($test)
{
  return \Staple\Inflector::pluralize($test);
}

function singular($test)
{
  return \Staple\Inflector::singularize($test);
}

function camelcase()
{
  return call_user_func_array('\\Staple\\Helpers::camelcase', func_get_args());
}

function underscore()
{
  return call_user_func_array('\\Staple\\Helpers::underscore', func_get_args());
}

function parameterize()
{
  return call_user_func_array('\\Staple\\Helpers::parameterize', func_get_args());
}

function titlecase()
{
  return call_user_func_array('\\Staple\\Helpers::titlecase', func_get_args());
}

function classify()
{
  return call_user_func_array('\\Staple\\Helpers::classify', func_get_args());
}

function slugify($text)
{
  return join('/', array_map('parameterize', explode('/', plain($text))));
}

function inspect($what)
{
  return \Symfony\Component\Yaml\Yaml::dump($what, 2, 2);
}

function render($file, array $vars = array())
{
  return \Tailor\Base::render($file, $vars);
}

function segment($nth)
{
  $set = explode('/', trim(URI, '/'));
  $out = isset($set[$nth - 1]) ? $set[$nth - 1] : FALSE;

  return $out;
}

function mdate()
{
  return call_user_func_array('\\Locale\\Datetime::format', func_get_args());
}

function fetch()
{
  return call_user_func_array('\\Staple\\Registry::fetch', func_get_args());
}

function exists()
{
  return call_user_func_array('\\Staple\\Registry::exists', func_get_args());
}

function remove()
{
  return call_user_func_array('\\Staple\\Registry::delete', func_get_args());
}

function assign()
{
  return call_user_func_array('\\Staple\\Registry::assign', func_get_args());
}

function paginate_to($url, $mapper, $current = 0, $limit = 10)
{
  // TODO: allow more params
  $pg = \Staple\Paginate::build();

  $pg->set('count_page', $limit);
  $pg->set('link_root', $url);

  $from = $pg->offset($mapper->count(), $current);
  return $pg->bind($mapper->offset($from)->limit($limit));
}

function remote_ip()
{
  return \Postman\Request::ip() ?: '0.0.0.0';
}
