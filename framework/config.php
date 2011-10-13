<?php

// defaults

$config['language'] = 'en';
$config['temporary_files'] = '/tmp';
$config['timezone'] = 'America/Mexico_City';
$config['allowed_chars'] = "$-_.+!*'(),";
$config['encoding'] = 'UTF-8';
$config['perms'] = 0755;


configure::filter('import_path', function ($value) {
  $value = (array) $value;
  $value = array_unique($value);

  return $value;
});

/* EOF: ./framework/config.php */
