<?php

require 'tetlphp/library/initialize.php';

config(dirname(__DIR__).DS.'config'.DS.'application'.EXT);
config(dirname(__DIR__).DS.'config'.DS.'database'.EXT);

import('tetl/mvc');

run(function()
{// TODO: implement UJS to catch PUT|DELETE
  require __DIR__.DS.'routes'.EXT;
});
