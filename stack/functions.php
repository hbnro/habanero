<?php

function say($text) {
  cli::writeln($text);
}

function yes($text) {
  return cli::choice($text, 'yn', 'n') === 'y';
}

function ask() {
  $args = func_get_args();
  return cli::apply('prompt', $args);
}

function choice() {
  $args = func_get_args();
  return cli::apply('choice', $args);
}

function menu() {
  $args = func_get_args();
  return cli::apply('menu', $args);
}

function done($text = 'done') {
  bold(ln($text));
}

function help() {
  cli::write(cli::format(app_generator::help(cli::flag('help'))));
}

function error($text) {
  cli::writeln(cli::format("\bred($text)\b"));
}

function info($text) {
  cli::writeln(cli::format("\bcyan($text)\b"));
}

function bold($text) {
  cli::writeln(cli::format("\bwhite($text)\b"));
}

function notice($text) {
  cli::writeln(cli::format("\byellow($text)\b"));
}

function success($text) {
  cli::writeln(cli::format("\bgreen($text)\b"));
}

function pretty($text) {
  ob_start() && $text();

  $text = preg_replace('/(\$?[\w.-]+)(?=\s=>)/', '\bcyan(\\1)\b', ob_get_clean());
  $text = preg_replace('/^\s*([\w:.-]+)(\s+)(.+?)$/m', '\bbrown(\\1)\b\\2\clight_gray(\\3)\c', $text);

  cli::write(cli::format($text));
}

function copy_file($to, $from) {
  status('copy', rtrim($to, DS).DS.basename($from));
  copy($from, mkpath($to).DS.basename($from));
}

function create_file($path, $text = '') {
  status('create', $path);
  write(mkpath(dirname($path)).DS.basename($path), $text);
}

function remove_file($path) {
  status('remove', $path);
  is_file($path) && unlink($path);
}

function create_dir($path) {
  status('create', "$path/");
  mkpath($path);
}

function copy_dir($to, $from) {
  status('copy', rtrim($to, DS).DS.basename($from).DS);
  cpfiles($from, $to.DS.basename($from), '*', TRUE);
}

function template($to, $from, array $vars = array()) {
  static $render = NULL;


  is_null($render) && $render = function() {
    ob_start();
    extract(func_get_arg(1));
    require func_get_arg(0);
    return ob_get_clean();
  };

  status('create', $to.DS.basename($from));
  write($to.DS.basename($from), $render($from, $vars));
}

function gsub_file($path, $regex, $replace, $position = 0) {
  if ( ! is_file($path)) {
    return FALSE;
  }

  return write($path, preg_replace_callback($regex, function ($match)
    use($replace, $position) {
    $tmp = is_closure($replace) ? $replace($match) : $replace;
    return ! $position ? $tmp : ($position < 0 ? "$tmp$match[0]" : "$match[0]$tmp");
  }, read($path)));
}

function append_file($path, $content) {
  return inject_into_file($path, $content, array('after' => '/$/s'));
}

function prepend_file($path, $content) {
  return inject_into_file($path, $content, array('before' => '/^/s'));
}

function inject_into_file($path, $content, array $params = array()) {
  $regex = '/$/s';

  if ( ! empty($params['unless'])) {
    if (preg_match($params['unless'], read($path))) {
      return FALSE;
    }
  }

  ! empty($params['after']) && $regex = $params['after'];
  ! empty($params['before']) && $regex = $params['before'];

  return gsub_file($path, $regex, $content, ! empty($params['before']) ? -1 : 1);
}

function add_class($path, $name, $parent = '', $methods = '', $properties = '') {
  $type   = $parent ? " extends $parent" : '';
  $props  =
  $method = '';

  if ( ! empty($methods)) {
    $test = array();
    foreach ((array) $methods as $one) {
      $test []= "  public static function $one() {\n  }\n";
    }
    $method = join("\n", $test);
  }

  if ( ! empty($properties)) {
    $test = array();
    foreach ((array) $properties as $one) {
      $test []= "  static $$one;\n";
    }
    $props = join("\n", $test);
    $props = "$props\n";
  }

  return write($path, "<?php\n\nclass $name$type\n{\n$props$method}\n");
}

function add_route($from, $to, $path = '', $method = 'get') {
  $path OR $path = "{$from}_$to";
  $text = ";\n$method('/$from', '$to', array('path' => '$path'));";
  return inject_into_file(APP_PATH.DS.'routes'.EXT, $text, array('before' => '/;[^;]*?$/'));
}

function add_view($parent, $name, $text = '', $ext = EXT) {
  return write(mkpath(APP_PATH.DS.'views'.DS.$parent).DS."$name.html$ext", $text);
}

function action($format, $text, $what) {
  $prefix = str_pad("\b$format($text)\b", 20 + strlen($format), ' ', STR_PAD_LEFT);
  $text   = str_replace(APP_PATH.DS, '', "\clight_gray($what)\c");

  cli::write(cli::format("$prefix  $text\n"));
}

function status($type, $text = '') {
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
      $text && $text = "  $text";
      $prefix = str_pad("\bwhite($type)\b", 25, ' ', STR_PAD_LEFT);

      cli::write(cli::format("$prefix$text\n"));
    break;
  }
}

/* EOF: ./stack/functions.php */
