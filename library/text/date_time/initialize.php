<?php

/**
 * Datetime initialization
 */

call_user_func(function () {
  /**
   * @ignore
   */

  require __DIR__.DS.'functions'.EXT;

  i18n::load_path(__DIR__.DS.'locale', 'date');
});

/* EOF: ./library/text/date_time/initialize.php */
