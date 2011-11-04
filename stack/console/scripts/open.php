<?php

info('Launching vhost');

$open_cmd = !! `whereis xdg-open` ? 'xdg-open' : 'open';

$dev_url  = sprintf('http://%s.dev/', basename(CWD));

success($dev_url);

system("$open_cmd $dev_url");

bold('Done');
