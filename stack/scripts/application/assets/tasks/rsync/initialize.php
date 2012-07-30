<?php

task('rsync', array(
  'default' => array(
    'desc' => 'Syncs the beta site',
    'exec' => function ($config) {
      $pwd = __DIR__;
      extract($config);
      notice('Deploying the beta site');
      system("rsync $beta_options --exclude-from $pwd/exclude.txt -e '{$beta_ssh_transport}' . {$beta_ssh_user}:{$beta_remote_root}");
    },
  ),
  'go' => array(
    'desc' => 'Syncs the live site',
    'exec' => function ($config) {
      $pwd = __DIR__;
      extract($config);
      notice('Deploying the final site');
      system("rsync $prod_options --exclude-from $pwd/exclude.txt -e '{$prod_ssh_transport}' . {$prod_ssh_user}:{$prod_remote_root}");
    },
  ),
));
