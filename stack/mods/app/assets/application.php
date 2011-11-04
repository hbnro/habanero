<?php

$config['rewrite'] = 0;

$config['temporary_files'] = dirname(__DIR__).DS.'tmp';

$config['environment'] = strpos(value($_SERVER, 'HTTP_HOST'), '.dev') ? 'development' : 'production';
