<?php

/**
 * CSS initialization
 */

call_user_func(function () {
  /**#@+
   * @ignore
   */
  require __DIR__.DS.'css'.EXT;

  class css_helper extends prototype
  {// fake class
  }

  // render callback
  if (class_exists('partial')) {
    partial::register('css', function ($file, array $vars = array()) {
      css::config('path', getcwd().DS.'views'.DS.'assets'.DS.'css');
      return css::render($file);
    });
  }

  // utility goodies
  require __DIR__.DS.'helpers'.DS.'color'.EXT;
  require __DIR__.DS.'helpers'.DS.'image'.EXT;
  require __DIR__.DS.'helpers'.DS.'number'.EXT;
  require __DIR__.DS.'helpers'.DS.'string'.EXT;
  /**#@-*/
});

/* EOF: ./library/css/initialize.php */
