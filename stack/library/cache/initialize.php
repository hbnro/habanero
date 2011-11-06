<?php

/**
 * Cache initialization
 */

call_user_func(function () {
  $aux = option('cache');
  $aux = is_empty($aux) ? 'php' : $aux;

  $driver_file = __DIR__.DS.'drivers'.DS.$aux.EXT;

  if ( ! is_file($driver_file)) {
    raise(ln('file_not_exists', array('name' => $driver_file)));
  }

  /**#@+
   * @ignore
   */

  require __DIR__.DS.'cache'.EXT;
  require $driver_file;

  /**#@-*/
});

/* EOF: ./stack/library/cache/initialize.php */
