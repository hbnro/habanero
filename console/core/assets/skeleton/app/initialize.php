<?php

require 'tetlphp/library/initialize.php';

config(dirname(__DIR__).DS.'config'.DS.'application'.EXT);
config(dirname(__DIR__).DS.'config'.DS.'database'.EXT);

import('tetl/mvc');

run(function()
{
  // handle assets
  get('/_/*path', function()
  {
    $res_file = dirname(__DIR__).DS.'assets'.DS.params('path');
    $res_file = strtr($res_file, '/', DS);

    if ( ! is_file($res_file))
    {
      status(404);
      exit;
    }

    // TODO: implement cache, gzip or anything better?
    $extension = ext($res_file);

    switch ($extension)
    {
      case 'jpeg';
      case 'jpg';
      case 'png';
      case 'gif';
        $mime_type = "image/$extension";
      break;
      case 'ico';
        $mime_type = 'image/x-icon';
      break;
      case 'css';
        $mime_type = 'text/css';
      break;
      case 'js';
        $mime_type = 'text/x-javascript';
      break;
      default;
        $mime_type = mime($res_file);
      break;
    }

    header("Content-Type: $mime_type");
    readfile($res_file);
    exit;
  });

  require __DIR__.DS.'routes'.EXT;
});
