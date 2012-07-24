<?php

# $config['rewrite'] = 1;
# $config['language'] = 'en';
# $config['timezone'] = 'UTC';
# $config['assets']['host'] = 'www.domain.tld';
# $config['security']['csrf_expire'] = 300;
# $config['database']['default'] = 'sqlite:'.APP_PATH.DS.'database'.DS.'db.sqlite';

$config['cli_imports'] = array('chess', 'coffee', 'tamal');

require __DIR__.DS.'config'.DS.'application'.EXT;
require __DIR__.DS.'config'.DS.'environments'.DS.APP_ENV.EXT;
