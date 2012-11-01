<?php

function run(\Closure $lambda)
{
  \Sauce\Base::initialize($lambda);
}

function option($get, $or = FALSE)
{
  return value(\Sauce\Config::all(), $get, $or);
}

function value($from, $that, $or = FALSE)
{
  return \Pallid\Helpers::fetch($from, $that, $or);
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
{ // TODO: mix with get/post vars?
  static $set = array();

  if ( ! func_num_args()) {
    return $set;
  } elseif (is_array($key)) {
    foreach ($key as $a => $value) {
      if (is_numeric($a)) {
        continue;
      }

      $set[trim($a)] = $value;
    }

    return TRUE;
  }

  return ! empty($set[$key]) ? $set[$key] : $default;
}

function ln($input)
{
  $args = func_get_args();

  if (is_array($input)) {
    foreach ($input as $key => $value) {
      $args[0] = $value;
      $input[$key] = call_user_func_array(__FUNCTION__, $args);
    }
  } else {
    $callback = is_numeric($input) ? 'pluralize' : 'translate';
    $input = call_user_func_array("\\Sauce\\I18n\\Base::$callback", $args);
  }

  return $input;
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

function redirect($path)
{
  $params = call_user_func_array(array(\Sauce\App\Bootstrap::instance()->response, 'redirect'), func_get_args());
  $output = new \Postman\Response($params);

  echo $output;

  exit;
}

function flash()
{
  return call_user_func_array('\\Web\\Session::flash', func_get_args());
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
  \Sauce\App\Assets::inline(join("\n", \Sauce\App\Assets::build($name, 'scripts_dir')), 'body');
}

function prepend_js($test)
{
  \Sauce\App\Assets::prepend($test, 'head');
}

function append_js($test)
{
  \Sauce\App\Assets::append($test, 'head');
}

function stylesheet_for($name)
{
  \Sauce\App\Assets::inline(join("\n", \Sauce\App\Assets::build($name, 'styles_dir')));
}

function prepend_css($test)
{
  \Sauce\App\Assets::prepend($test, 'head');
}

function append_css($test)
{
  \Sauce\App\Assets::append($test, 'head');
}

function csrf_meta_tag()
{
  return \Labourer\Web\Html::meta('csrf-token', \Labourer\Web\Session::token());
}

function tag_for($src)
{
  return \Sauce\App\Assets::tag_for($src);
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
  } else {
    $attrs['alt'] = $attrs['title'] = $alt ?: $src;
  }


  if ($img = \Tailor\Helpers::image($src)) {
    $attrs['width'] = $img['dims'][0];
    $attrs['height'] = $img['dims'][1];
    $attrs['src'] = asset_url($src);
  } else {
    $attrs['src'] = $src;
  }

  return \Labourer\Web\Html::tag('img', $attrs);
}

function partial($path, array $vars = array())
{
  if ($tpl = \Tailor\Base::partial($path)) {
    return \Tailor\Base::render($tpl, $vars);
  }
  throw new \Exception("Partial view '$path' does not exists");
}

function write($file, $content, $append = FALSE)
{
  return \IO\File::write($file, $content, $append);
}

function read($file)
{
  return \IO\File::read($file);
}

function tag($name, array $attrs = array())
{
  return call_user_func_array("\\Labourer\\Web\\Html::$name", $attrs);
}

function e($text)
{
  return \Labourer\Web\Html::ents($text, TRUE);
}
