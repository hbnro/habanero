<?php

info(ln('tetl.interactive_mode'));
cli::writeln();

cli::main(function()
{

  $input = cli::readln('> ');

  // TODO: what to do?

  if (in_array($input, array('q', 'quit', 'exit')))
  {
    cli::quit();
  }

});

cli::writeln();
bold(ln('tetl.done'));

exit;
