<?php

/**
 * Model initialization
 */

// autoload
rescue(function ($class) {
  /**
    * @ignore
    */
  $model_file  = CWD.DS.'app'.DS.'models'.DS.$class.EXT;
  $driver_file = __DIR__.DS.'drivers'.DS.$class.EXT;

  if (is_file($driver_file)) {
    /**#@+
     * @ignore
     */
    require __DIR__.DS.'record'.EXT;
    require __DIR__.DS.'relation'.EXT;
    /**#@-*/

    require $driver_file;
  }
  is_file($model_file) && require $model_file;
  /**#@-*/
});

/* EOF: ./stack/library/app/base/model/initialize.php */
