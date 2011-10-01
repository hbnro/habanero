<?php

// development
$ssh_user_beta = 'root@domain.tld';
$remote_root_beta = '/var/www/vhosts/domain.tld/httpdocs/';
$ssh_transport_beta = 'ssh';

$params['default'] = function()
  use($ssh_user_beta, $remote_root_beta, $ssh_transport_beta)
{
  notice('Simulating a deploy to the beta site');
  system("rsync --dry-run -avzlC --exclude-from rsync/exclude.txt --stats --progress --delete -e '{$ssh_transport_beta}' . {$ssh_user_beta}:{$remote_root_beta}");
};

$params['dev'] = function()
  use($ssh_user_beta, $remote_root_beta, $ssh_transport_beta)
{
  notice('Deploying the beta site');
  system("rsync -avzlC --exclude-from rsync/exclude.txt --stats --progress --delete -e '{$ssh_transport_beta}' . {$ssh_user_beta}:{$remote_root_beta}");
};


// production
$ssh_user = 'root@domain.tld';
$remote_root = '/var/www/vhosts/domain.tld/httpdocs/';
$ssh_transport = 'ssh';

$params['prod'] = function()
    use($ssh_user, $remote_root, $ssh_transport)
{
  notice('Simulating a deploy to the live site');
  system("rsync --dry-run -avzlC --exclude-from rsync/exclude.txt --stats --progress --delete -e '{$ssh_transport}' . {$ssh_user}:{$remote_root}");
};

$params['deploy'] = function()
    use($ssh_user, $remote_root, $ssh_transport)
{
  notice('Deploying the live site');
  system("rsync -avlzC --exclude-from rsync/exclude.txt --progress --stats --delete -e '{$ssh_transport}' . {$ssh_user}:{$remote_root}");
};
