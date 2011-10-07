<?php

call_user_func(function()
{
  require 'tetlphp/library/initialize.php';

  config(__DIR__.DS.'config'.DS.'application'.EXT);
  config(__DIR__.DS.'config'.DS.'environments'.DS.option('environment').EXT);

  $import_path   = (array) option('import_path', array());
  $import_path []= dirname(LIB).DS.'stack'.DS.'lib';
  $import_path []= __DIR__.DS.'lib';


  config('import_path', $import_path);

  import('app/mvc');

  run(function()
  {
    require __DIR__.DS.'app'.DS.'helpers'.EXT;
    require __DIR__.DS.'app'.DS.'routes'.EXT;

    i18n::load_path(__DIR__.DS.'locale');
  });
});
