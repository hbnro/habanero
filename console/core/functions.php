<?php

function help()
{
  $introduction = ln('tetl.generator_intro');
  $available = ln('tetl.available_mods');

  $app_title = ln('tetl.app_generator');
  $db_title = ln('tetl.db_generator');
  $c_title = ln('tetl.interactive');

  $str = <<<HELP

  $introduction

  $available:

  \bred(app)\b $app_title
  \bred(db)\b $db_title
  \bred(s)\b $c_title

HELP;

  cli::write(cli::format("$str\n"));
}

function red($text)
{
  cli::writeln(cli::format("\bred($text)\b"));
}

function blue($text)
{
  cli::writeln(cli::format("\bblue($text)\b"));
}

function white($text)
{
  cli::writeln(cli::format("\bwhite($text)\b"));
}

function yellow($text)
{
  cli::writeln(cli::format("\byellow($text)\b"));
}

function green($text)
{
  cli::writeln(cli::format("\bgreen($text)\b"));
}
