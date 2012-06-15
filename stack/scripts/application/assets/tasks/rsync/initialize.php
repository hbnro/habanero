<?php

i18n::load_path(__DIR__.DS.'locale', 'rsync');

app_generator::task('rsync', array(
  'default' => array(
    'desc' => ln('rsync.default_title'),
    'exec' => function ($config) {
      $pwd = __DIR__;
      extract($config);
      notice(ln('rsync.default_deploy'));
      system("rsync $beta_options --exclude-from $pwd/exclude.txt -e '{$beta_ssh_transport}' . {$beta_ssh_user}:{$beta_remote_root}");
    },
  ),
  'go' => array(
    'desc' => ln('rsync.final_title'),
    'exec' => function ($config) {
      $pwd = __DIR__;
      extract($config);
      notice(ln('rsync.final_deploy'));
      system("rsync $prod_options --exclude-from $pwd/exclude.txt -e '{$prod_ssh_transport}' . {$prod_ssh_user}:{$prod_remote_root}");
    },
  ),
));
