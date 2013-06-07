<?php

// routes
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

// helpers
function method()
{
  return \Postman\Request::method();
}

function server()
{
  return call_user_func_array('\\Postman\\Request::env', func_get_args());
}

function remote_ip()
{
  return \Postman\Request::ip() ?: '0.0.0.0';
}

function segment($nth)
{
  $set = explode('/', trim(URI, '/'));
  $out = isset($set[$nth - 1]) ? $set[$nth - 1] : FALSE;

  return $out;
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
