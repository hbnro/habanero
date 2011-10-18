<?php

// defaults

$config['language'] = 'en';
$config['temporary_files'] = '/tmp';
$config['timezone'] = 'America/Mexico_City';
$config['allowed_chars'] = "$-_.+!*'(),";
$config['encoding'] = 'UTF-8';
$config['perms'] = 0755;


// autoload
config('import_path', array(
  dirname(LIB).DS.'stack'.DS.'library',
  dirname(LIB).DS.'library',
));

/* EOF: ./framework/config.php */
