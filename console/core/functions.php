<?php

function help()
{
  $introduction = ln('tetl.generator_intro');

  $app_title = ln('tetl.application_generator');
  $db_title = ln('tetl.database_generator');

  $str = <<<HELP

  $introduction

  \cred(app)\c $app_title
   \cred(db)\c $db_title

HELP;

  cli::write(cli::format("$str\n"));
}

function error($text)
{
  cli::writeln(cli::format("\cred($text)\c"));
}

function info($text)
{
  cli::writeln(cli::format("\ccyan($text)\c"));
}

function bold($text)
{
  cli::writeln(cli::format("\cwhite($text)\c"));
}

function notice($text)
{
  cli::writeln(cli::format("\cyellow($text)\c"));
}

function success($text)
{
  cli::writeln(cli::format("\cgreen($text)\c"));
}
