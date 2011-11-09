<?php

function say($text) {
  cli::writeln($text);
}

function yes($text) {
  return cli::option($text, 'yn', 'n') === 'y';
}

function done($text = 'done') {
  bold(ln($text));
}
// TODO; choice, prompt, cli-tools

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
  $text = preg_replace('/^([\w.-]+)(\s+)(.+?)$/m', '\bblue(\\1)\b\\2\clight_gray(\\3)\c', $text);

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
  status('copy', basename($from) . '/');
  cpfiles($from, $to.DS.basename($from), '*', TRUE);
}

function template($to, $from, array $vars = array()) {
  status('create', $to.DS.basename($from));

  $render = function()
  {
    ob_start();
    extract(func_get_arg(1));
    require func_get_arg(0);
    return ob_get_clean();
  };

  write($to.DS.basename($from), $render($from, $vars));
}

function action($format, $text, $what) {
  $prefix = str_pad("\b$format($text)\b", 20 + strlen($format), ' ', STR_PAD_LEFT);
  $text   = str_replace(getcwd().DS, '', "\clight_gray($what)\c");

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
