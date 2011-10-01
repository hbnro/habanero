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

/* EOF: ./cli/functions.php */
