<?php

$config['database'] = str_replace('postgres:', 'pgsql:', getenv('SHARED_DATABASE_URL'));
$config['rewrite'] = 1;
