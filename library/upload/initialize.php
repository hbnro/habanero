<?php

/**
 * Upload initialization
 */

/**#@+
 * Upload error constants
 */
define('UPLOAD_ERR_PATH', 9);
define('UPLOAD_ERR_MULTI', 10);
define('UPLOAD_ERR_MIN_SIZE', 11);
define('UPLOAD_ERR_MAX_SIZE', 12);
define('UPLOAD_ERR_TYPE', 13);
define('UPLOAD_ERR_EXT', 14);
/**#@-*/


/**
 * @ignore
 */

require __DIR__.DS.'upload'.EXT;

i18n::load_path(__DIR__.DS.'locale', 'upload');

/* EOF: ./library/upload/initialize.php */
