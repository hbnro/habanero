<?php

rescue(function($class)
{
  /**
    * @ignore
    */

  switch ($class)
  {
    case 'dbmodel';
      import('tetl/db');
    case 'mongdel';
      require __DIR__.DS.'drivers'.DS.$class.EXT;
    break;
    case 'model';
      require __DIR__.DS.'system'.EXT;
    break;
    default;
      $model_file = CWD.DS.'app'.DS.'models'.DS.$class.EXT;

      if (is_file($model_file))
      {
        require $model_file;
      }
    break;
  }
  /**#@-*/
});
