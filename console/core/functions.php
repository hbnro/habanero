<?php

function help()
{
  $introduction = ln('tetl.generator_intro');

  $app_title = ln('tetl.application_generator');
  $db_title = ln('tetl.database_generator');
  $s_title = ln('tetl.interactive_mode');

  $str = <<<HELP

  $introduction

  \bdark_gray(tetl)\b \bred(app|application)\b $app_title
  \bdark_gray(tetl)\b \bred(db|database)\b $db_title
  \bdark_gray(tetl)\b \bred(s|console)\b $s_title

HELP;

  cli::write(cli::format("$str\n"));
}

function error($text)
{
  cli::writeln(cli::format("\bred($text)\b"));
}

function info($text)
{
  cli::writeln(cli::format("\bblue($text)\b"));
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
