<?php

// defaults

$config['language'] = 'en';
$config['temporary_files'] = '/tmp';
$config['timezone'] = 'America/Mexico_City';

// autoload
config('import_path', array(
  dirname(LIB).DS.'library',
));

/* EOF: ./framework/config.php */
