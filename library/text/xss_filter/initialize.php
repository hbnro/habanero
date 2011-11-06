<?php

/**
 * XSS initialization
 */

call_user_func(function () {
  /**
   * @ignore
   */

  require __DIR__.DS.'xss_filter'.EXT;
});

/* EOF: ./library/text/xss_filter/initialize.php */
