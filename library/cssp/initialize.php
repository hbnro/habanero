<?php

/**
 * CSS initialization
 */

call_user_func(function () {
  /**#@+
   * @ignore
   */
  require __DIR__.DS.'cssp'.EXT;

  class cssp_helper extends prototype
  {// fake class
  }

  // render callback
  if (class_exists('partial')) {
    partial::register('cssp', function ($file, array $vars = array()) {
      cssp::config('path', APP_PATH.DS.'views'.DS.'assets'.DS.'css');
      return cssp::render($file);
    });
  }

  // asset compiler
  if (class_exists('assets')) {
    assets::compile('cssp', function ($file) {
      return partial::render($file);
    });
  }

  // utility goodies
  require __DIR__.DS.'helpers'.DS.'color'.EXT;
  require __DIR__.DS.'helpers'.DS.'image'.EXT;
  require __DIR__.DS.'helpers'.DS.'number'.EXT;
  require __DIR__.DS.'helpers'.DS.'string'.EXT;
  /**#@-*/
});

/* EOF: ./library/cssp/initialize.php */
