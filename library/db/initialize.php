<?php

/**
 * Database initialization
 */

define('ALL', '*');
define('ASC', 'ASC');
define('DESC', 'DESC');
define('RANDOM', 'RANDOM');

define('IS_NULL', NULL);
define('NOT_NULL', "<> ''");

define('AS_ARRAY', 'AS_ARRAY');
define('AS_OBJECT', 'AS_OBJECT');

i18n::load_path(__DIR__.DS.'locale', 'db');

/**#@+
 * @ignore
 */
require __DIR__.DS.'db'.EXT;

require __DIR__.DS.'sql_raw'.EXT;
require __DIR__.DS.'sql_base'.EXT;
require __DIR__.DS.'sql_query'.EXT;
require __DIR__.DS.'sql_scheme'.EXT;

require __DIR__.DS.'migration'.EXT;
/**#@-*/

/* EOF: ./library/db/initialize.php */
