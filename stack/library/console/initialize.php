<?php

/**
 * Console initialization
 */

call_user_func(function () {
  /**
   * @ignore
   */
  require __DIR__.DS.'cli'.EXT;

  i18n::load_path(__DIR__.DS.'locale', 'cli');
});

/* EOF: ./stack/library/console/initialize.php */
