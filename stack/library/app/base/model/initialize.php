<?php

/**
 * Model initialization
 */

/**#@+
 * @ignore
 */
require __DIR__.DS.'system'.EXT;
require __DIR__.DS.'relation'.EXT;
/**#@-*/



// autoload
rescue(function ($class) {
  /**
    * @ignore
    */
  $model_file = CWD.DS.'app'.DS.'models'.DS.$class.EXT;
  $driver_file = __DIR__.DS.'drivers'.DS.$class.EXT;

  is_file($driver_file) && require $driver_file;
  is_file($model_file) && require $model_file;
  /**#@-*/
});

/* EOF: ./stack/library/app/base/model/initialize.php */
