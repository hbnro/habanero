<?php

require 'tetlphp/library/initialize.php';

config(dirname(__DIR__).DS.'config'.DS.'application'.EXT);
config(dirname(__DIR__).DS.'config'.DS.'database'.EXT);

import('tetl/mvc');

run(function()
{
  require dirname(__DIR__).DS.'app'.DS.'routes'.EXT;
});
