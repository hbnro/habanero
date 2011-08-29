<?php

blue(ln('tetl.interactive_mode'));

cli::main(function()
{
  
  $input = cli::readln('> ');
  
  // TODO: what to do?
  
  if (in_array($input, array('q', 'quit', 'exit')))
  {
    cli::quit();
  }
  
});

white(ln('tetl.done'));

exit;
