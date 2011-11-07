<?php

/**
 * CSS initialization
 */

call_user_func(function () {
  /**#@+
   * @ignore
   */
  require __DIR__.DS.'tsss'.EXT;

  class css_helper extends prototype
  {// fake class
  }

  // utility goodies
  require __DIR__.DS.'helpers'.DS.'color'.EXT;
  require __DIR__.DS.'helpers'.DS.'image'.EXT;
  require __DIR__.DS.'helpers'.DS.'number'.EXT;
  require __DIR__.DS.'helpers'.DS.'string'.EXT;
  /**#@-*/
});

/* EOF: ./library/tsss/initialize.php */
