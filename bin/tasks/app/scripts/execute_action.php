<?php

$method = 'GET';

arg('g get') && $method = 'GET';
arg('u put') && $method = 'PUT';
arg('p post') && $method = 'POST';
arg('d delete') && $method = 'DELETE';


$uri =  array_shift($params) ?: '/';

arg('u uri') && $method = arg('u uri');

$file = FALSE;

arg('f file') && $file = arg('f file');

if ($file !== FALSE) {
  if ( ! is_file($file)) {
    die('FILE!!!');
  }
}

$file = realpath($file);

echo "URI: $uri FILE: $file METHOD: $method";
