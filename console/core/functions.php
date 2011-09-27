<?php

function help()
{
  $introduction = ln('tetl.generator_intro');

  $app_title = ln('tetl.application_generator');
  $db_title = ln('tetl.database_generator');

  $str = <<<HELP

  $introduction

  \bgreen(app)\b $app_title
   \bgreen(db)\b $db_title

HELP;

  cli::write(cli::format("$str\n"));
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

  return $text;
}
