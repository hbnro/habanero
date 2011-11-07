<?php

/**
 * Partial initialization
 */

call_user_func(function () {
  i18n::load_path(__DIR__.DS.'locale', 'partial');

  /**#@+
   * @ignore
   */
  require __DIR__.DS.'functions'.EXT;
  require __DIR__.DS.'partial'.EXT;
  /**#@-*/


  // render callback
  partial::register('php', function ($file, array $vars = array()) {
    return render($file, TRUE, array(
        'locals' => $vars,
      ));
  });
});

/* EOF: ./library/partial/initialize.php */
