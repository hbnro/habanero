<?php

/**
 * Facebook initialization
 */
call_user_func(function () {
  /**#@+
   * @ignore
   */
  require __DIR__.DS.'fb'.EXT;
  require __DIR__.DS.'helpers'.EXT;
  require __DIR__.DS.'vendor'.DS.'facebook'.EXT;
  /**#@-*/
});

/* EOF: ./library/facebook/initialize.php */