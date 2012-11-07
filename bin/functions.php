<?php

function arg()
{
  return \Sauce\Shell\CLI::flag(func_get_args());
}

function flags()
{
  return \Sauce\Shell\CLI::args();
}

function readln($input = "\n")
{
  return \Sauce\Shell\CLI::readln($input);
}

function writeln($input = "\n")
{
  return \Sauce\Shell\CLI::writeln($input);
}

function colorize($input)
{
  return \Sauce\Shell\CLI::format($input);
}

function choice()
{
  return call_user_func_array('\\Sauce\\Shell\\CLI::choice', func_get_args());
}

function prompt()
{
  return call_user_func_array('\\Sauce\\Shell\\CLI::prompt', func_get_args());
}

function menu()
{
  return call_user_func_array('\\Sauce\\Shell\\CLI::menu', func_get_args());
}

function ask() {
  call_user_func_array('prompt', func_get_args());
}

function say($text)
{
  writeln(colorize($text));
}

function yes($text)
{
  return choice($text, 'yn', 'n') === 'y';
}

function help($cmd = NULL)
{
  \Sauce\Shell\Task::help($cmd);
}

function task($name, $desc, \Closure $fn)
{
  \Sauce\Shell\Task::task($name, array('desc' => $desc, 'exec' => $fn));
}

function error($text)
{
  \Sauce\Shell\CLI::error("\bred($text)\b");
}

function info($text)
{
  writeln(colorize("\bcyan($text)\b"));
}

function bold($text)
{
  writeln(colorize("\bwhite($text)\b"));
}

function notice($text)
{
  writeln(colorize("\byellow($text)\b"));
}

function success($text)
{
  writeln(colorize("\bgreen($text)\b"));
}

function pretty($text)
{
  ob_start() && $text();

  $text = preg_replace('/(\$?[\w.-]+)(?=\s=>)/', '\bcyan(\\1)\b', ob_get_clean());
  $text = preg_replace('/^\s*([\w:.-]+)(\s+)(.+?)$/m', '\bbrown(\\1)\b\\2\clight_gray(\\3)\c', $text);

  writeln(colorize($text));
}

function copy_file($to, $from, $perms = FALSE)
{
  status('copy', path(rtrim($to, DIRECTORY_SEPARATOR), basename($from)));

  is_dir($to) OR mkdir($to, $perms ?: 0755, TRUE);
  copy($from, path($to, basename($from)));
}

function create_file($path, $text = '', $perms = FALSE)
{
  status('create', $path);

  is_dir($dir = dirname($path)) OR mkdir($dir, $perms ?: 0755, TRUE);

  write($path, $text);
  $perms && chmod($path, $perms);
}

function remove_file($path)
{
  status('remove', $path);
  is_file($path) && unlink($path);
}

function create_dir($path, $perms = FALSE)
{
  status('create', $path);
  is_dir($path) OR mkdir($path, $perms ?: 0755, TRUE);
}

function copy_dir($to, $from)
{
  status('copy', path(rtrim($to, DIRECTORY_SEPARATOR), basename($from)));
  \IO\Dir::cpfiles($from, path($to, basename($from)), '*', TRUE);
}

function template($from, array $vars = array())
{
  return call_user_func(function () {
      ob_start();
      extract(func_get_arg(1));
      require func_get_arg(0);
      return ob_get_clean();
    }, $from, $vars);
}

function append_file($path, $content, array $params = array())
{
  status('append', $path);
  empty($params['after']) && $params['after'] = '/.*$/s';
  return inject_into_file($path, $content, $params);
}

function prepend_file($path, $content, array $params = array())
{
  status('prepend', $path);
  empty($params['before']) && $params['before'] = '/^.*/s';
  return inject_into_file($path, $content, $params);
}

function gsub_file($path, $regex, $replace, $position = 0)
{
  if ( ! is_file($path)) {
    return FALSE;
  }


  $content = read($path);

  if (preg_match($regex, $content, $match)) {
    $replace = $replace instanceof \Closure ? $replace($match) : $replace;
    $replace = $position < 0 ? "$replace$match[0]" : ($position > 0 ? "$match[0]$replace" : $replace);
    $content = str_replace($match[0], $replace, $content);

    return write($path, $content);
  }
}

function inject_into_file($path, $content, array $params = array())
{
  $regex = '/$/s';

  if ( ! empty($params['unless'])) {
    if (preg_match($params['unless'], read($path))) {
      return FALSE;
    }
  }

  ! empty($params['after']) && $regex = $params['after'];
  ! empty($params['before']) && $regex = $params['before'];

  return gsub_file($path, $regex, $content, ! empty($params['before']) ? -1 : ( ! empty($params['after']) ? 1 : 0));
}

function add_class($path, $name, $parent = '', $methods = '', array $properties = array(), array $constants = array())
{
  $type   = $parent ? " extends $parent" : '';
  $props  =
  $consts =
  $method = '';

  if ( ! empty($constants)) {
    $test = array();
    foreach ($constants as $one => $val) {
      $one = strtoupper($one);
      $test []= "  const $one = '$val';\n";
    }
    $consts = join("\n", $test);
  }

  if ( ! empty($methods)) {
    $test = array();
    foreach ((array) $methods as $one) {
      $prefix = '';

      if (strpos($one, ' ') !== FALSE) {
        @list($prefix, $one) = explode($one, 2);
      }

      $test []= "  {$prefix}function $one() {\n  }\n";
    }
    $method = join("\n", $test);
  }

  if ( ! empty($properties)) {
    $test = array();
    foreach ($properties as $key => $val) {
      $prefix = 'public';

      if (strpos($key, ' ') !== FALSE) {
        $parts  = explode(' ', $key);
        $key    = array_pop($parts);
        $prefix = join(' ', $parts);
      }

      $test []= "  $prefix $$key = '$val';";
    }
    $props = join("\n", $test);
    $props = "$props\n";
  }

  return write($path, "<?php\n\nclass $name$type\n{\n$consts$props$method}\n");
}

function add_route($from, $to, $path = '', $method = 'get')
{
  $path OR $path = "{$from}_$to";
  $text = ";\n$method('/$from', '$to', array('path' => '$path'));";
  is_dir($dir = path(APP_PATH, 'config')) OR mkdir($dir, 0755, TRUE);
  return inject_into_file(path($dir, 'routes.php'), $text, array('before' => '/;[^;]*?$/'));
}

function add_view($parent, $name, $text = '')
{
  is_dir($path = path(APP_PATH, 'views', $parent)) OR mkdir($path, 0755, TRUE);
  return write(path($path, $name), $text);
}

function action($format, $text, $what)
{
  $prefix = str_pad("\b$format($text)\b", 20 + strlen($format), ' ', STR_PAD_LEFT);
  $text   = str_replace(APP_PATH.DIRECTORY_SEPARATOR, '', "\clight_gray($what)\c");

  writeln(colorize("$prefix  $text"));
}

function status($type, $text = '')
{
  $text = str_replace(APP_PATH.DIRECTORY_SEPARATOR, '', "  $text");

  switch ($type) {
    case 'create';
      action('green', $type, $text);
    break;
    case 'remove';
      action('red', $type, $text);
    break;
    case 'rename';
      action('cyan', $type, $text);
    break;
    case 'update';
      action('white', $type, $text);
    break;
    case 'copy';
      action('yellow', $type, $text);
    break;
    default;
      $prefix = str_pad("\bwhite($type)\b", 25, ' ', STR_PAD_LEFT);
      writeln(colorize("$prefix$text"));
    break;
  }
}
