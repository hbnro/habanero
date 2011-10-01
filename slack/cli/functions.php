<?php

function help($test)
{
  $str  = sprintf("\n  %s\n", ln('tetl.generator_intro'));

  foreach ($test as $one)
  {
    $ns = basename($one);

    i18n::load_path($one.DS.'locale', $ns);

    $str .= sprintf("\n  %20s \clight_gray(%s)\c", "\bgreen($ns)\b", ln("$ns.generator_intro"));
  }

  cli::write(cli::format("$str\n\n"));
}

function error($text)
{
  cli::writeln(cli::format("\bred($text)\b"));
}

function info($text)
{
  cli::writeln(cli::format("\bcyan($text)\b"));
}

function bold($text)
{
  cli::writeln(cli::format("\bwhite($text)\b"));
}

function notice($text)
{
  cli::writeln(cli::format("\byellow($text)\b"));
}

function success($text)
{
  cli::writeln(cli::format("\bgreen($text)\b"));
}

function pretty($text)
{
  ob_start() && $text();

  $text = preg_replace('/\b([\w.-]+)(?=\s=>)/', '\bcyan(\\1)\b', ob_get_clean());
  $text = preg_replace('/^([\w.-]+)(\s+)(.+?)$/m', '\bblue(\\1)\b\\2\clight_gray(\\3)\c', $text);

  cli::write(cli::format($text));
}

function copy_file($to, $from)
{
  status('copy', rtrim($to, DS).DS.basename($from));
  copy($from, mkpath($to).DS.basename($from));
}

function create_file($path, $text = '')
{
  status('create', $path);
  write(mkpath(dirname($path)).DS.basename($path), $text);
}

function remove_file($path)
{
  status('remove', $path);
  is_file($path) && unlink($path);
}

function create_dir($path)
{
  status('create', $path);
  mkpath($path);
}

function copy_dir($to, $from)
{
  status('copy', rtrim($to, DS).DS.basename($from));
  cpfiles($from, $to, '*', TRUE);
}

function action($format, $text, $what)
{
  $prefix = str_pad("\b$format($text)\b", 20 + strlen($format), ' ', STR_PAD_LEFT);
  $text   = str_replace(CWD.DS, '', "\clight_gray($what)\c");

  cli::write(cli::format("$prefix  $text\n"));
}

function status($type, $text)
{
  switch ($type)
  {
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
    default:
      $prefix = str_pad("\bwhite($type)\b", 25, ' ', STR_PAD_LEFT);
      cli::write(cli::format("$prefix  $text\n"));
    break;
  }
}

/* EOF: ./cli/functions.php */
