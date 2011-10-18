<?php

call_user_func(function()
{
  require 'tetlphp/framework/initialize.php';

  import('app/base');

  run(function()
  {
    require __DIR__.DS.'app'.DS.'helpers'.EXT;
    routing::load(__DIR__.DS.'app'.DS.'routes'.EXT, array('safe' => TRUE));
  });
});
