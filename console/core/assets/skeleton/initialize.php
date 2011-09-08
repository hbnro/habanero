<?php

call_user_func(function()
{
  require 'tetlphp/library/initialize.php';

  config(__DIR__.DS.'config'.DS.'application'.EXT);
  config(__DIR__.DS.'config'.DS.'database'.EXT);

  config(__DIR__.DS.'config'.DS.'environments'.DS.option('environment').EXT);


  import('tetl/mvc');

  $bootstrap = bootstrap::methods();

  bootstrap::implement('raise', function($message)
    use($bootstrap)
  {
    $error_status = 500;

    switch (option('environment'))
    {
      case 'development';
        $bootstrap['raise']($message);
      break;
      case 'production';
      case 'testing';
      default;
        if (preg_match('/^(?:GET|PUT|POST|DELETE)\s+\/.+?$/', $message))
        {
          $error_status = 404;
        }
      break;
    }


    $error_file   = __DIR__.DS.'app'.DS.'views'.DS.'errors'.DS."$error_status.html".EXT;

    response(render($error_file, TRUE), array(
      'status' => $error_status,
      'message' => $message,
    ));
  });


  run(function()
  {
    require __DIR__.DS.'app'.DS.'helpers'.EXT;
    require __DIR__.DS.'app'.DS.'routes'.EXT;
  });
});
