<?php

require 'tetlphp/library/initialize.php';

config(__DIR__.DS.'config'.DS.'application'.EXT);
config(__DIR__.DS.'config'.DS.'database'.EXT);

import('tetl/mvc');

run(function()
{
  require __DIR__.DS.'app'.DS.'helpers'.EXT;
  require __DIR__.DS.'app'.DS.'routes'.EXT;
});
