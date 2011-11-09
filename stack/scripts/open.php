<?php

info(ln('launch_vhost'));

$open_cmd = !! `whereis xdg-open` ? 'xdg-open' : 'open';

$dev_url  = sprintf('http://%s.dev/', basename(getcwd()));

success($dev_url);

system("$open_cmd $dev_url");

done();

/* EOF: ./stack/scripts/open.php */
