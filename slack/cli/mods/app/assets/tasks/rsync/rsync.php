<?php

i18n::load_path(__DIR__.DS.'locale', 'rsync');


// development
$ssh_user_beta = 'root@domain.tld';
$remote_root_beta = '/var/www/vhosts/domain.tld/httpdocs/';
$ssh_transport_beta = 'ssh';

$params['default'] = function()
  use($ssh_user_beta, $remote_root_beta, $ssh_transport_beta)
{
  notice(ln('rsync.default_deploy'));
  system("rsync --dry-run -avzlC --exclude-from rsync/exclude.txt --stats --progress --delete -e '{$ssh_transport_beta}' . {$ssh_user_beta}:{$remote_root_beta}");
};

$params['dev'] = function()
  use($ssh_user_beta, $remote_root_beta, $ssh_transport_beta)
{
  notice(ln('rsync.development_deploy'));
  system("rsync -avzlC --exclude-from rsync/exclude.txt --stats --progress --delete -e '{$ssh_transport_beta}' . {$ssh_user_beta}:{$remote_root_beta}");
};


// production
$ssh_user = 'root@domain.tld';
$remote_root = '/var/www/vhosts/domain.tld/httpdocs/';
$ssh_transport = 'ssh';

$params['prod'] = function()
    use($ssh_user, $remote_root, $ssh_transport)
{
  notice(ln('rsync.production_deploy'));
  system("rsync --dry-run -avzlC --exclude-from rsync/exclude.txt --stats --progress --delete -e '{$ssh_transport}' . {$ssh_user}:{$remote_root}");
};

$params['deploy'] = function()
    use($ssh_user, $remote_root, $ssh_transport)
{
  notice(ln('rsync.final_deploy'));
  system("rsync -avlzC --exclude-from rsync/exclude.txt --progress --stats --delete -e '{$ssh_transport}' . {$ssh_user}:{$remote_root}");
};
