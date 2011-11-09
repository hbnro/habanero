<?php

/**
 * A record initialization
 */

call_user_func(function () {
  i18n::load_path(__DIR__.DS.'locale', 'ar');

  /**#@+
   * @ignore
   */
  require __DIR__.DS.'a_record'.EXT;
  require __DIR__.DS.'a_relation'.EXT;
  /**#@-*/


  // autoload
  rescue(function ($class) {
    /**
      * @ignore
      */
    $model_file  = getcwd().DS.'models'.DS.$class.EXT;
    $driver_file = __DIR__.DS.'drivers'.DS.$class.EXT;

    is_file($driver_file) && require $driver_file;
    is_file($model_file) && require $model_file;
    /**#@-*/
  });
});

/* EOF: ./library/a_record/initialize.php */
