<?php

$config['environment'] = strpos(value($_SERVER, 'HTTP_HOST'), '.com') ? 'production' : 'development';
$config['database'] = 'sqlite:'.getcwd().DS.'database'.DS.'db.sqlite';
$config['rewrite'] = 0;
