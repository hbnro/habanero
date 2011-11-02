<?php

rsync_task::config(function($env) {

  $env->dev = array(
    'ssh_user_beta' => 'root@domain.tld',
    'remote_root_beta' => '/var/www/vhosts/domain.tld/httpdocs/',
    'ssh_transport_beta' => 'ssh',
  );

  $env->prod = array(
    'ssh_user' => 'root@domain.tld',
    'remote_root' => '/var/www/vhosts/domain.tld/httpdocs/',
    'ssh_transport' => 'ssh',
  );

});
