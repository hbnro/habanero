<?php

$config['environment'] = strpos(value($_SERVER, 'HTTP_HOST'), '.com') ? 'production' : 'development';
