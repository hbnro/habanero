<?php

# $config['rewrite'] = 1;
# $config['language'] = 'en';
# $config['timezone'] = 'UTC';

$config['cli_imports'] = array('chess', 'coffee', 'tamal');

require __DIR__.DS.'config'.DS.'application'.EXT;
require __DIR__.DS.'config'.DS.'environments'.DS.APP_ENV.EXT;
