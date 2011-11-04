<?php

info("Verifying vhost availability");

$paths = array(
  '/etc/apache2/sites-available',
  '/etc/apache2/extra/httpd-vhosts.conf',
  '/private/etc/apache2/virtualhosts',
  '/private/etc/apache2/extra/httpd-vhosts.conf',
);


foreach ($paths as $one) {
  if (file_exists($one)) {
    $vhost_path = $one;
    break;
  }
}

if (empty($vhost_path)) {
  error('Not found a suitable vhost configuration on your system!');
} else {
  is_dir($vhost_path) ? vhost_create($vhost_path) : vhost_write($vhost_path);
}

bold('Done');


function vhost_write($vhost_path) {
  $base_path  = CWD;
  $base_name = basename($base_path);
  notice("TODO: insert/remove vhost-def to $vhost_path");
}

function vhost_create($vhost_path) {
  $base_path  = CWD;
  $base_name  = basename($base_path);
  $vhost_file = "$vhost_path/$base_name";

  if (cli::flag('remove')) {
    if ( ! is_file($vhost_file)) {
      error("Not exists $vhost_file");
    } else {
      notice("Removing $vhost_file");
      unlink($vhost_file);
      sleep(1);

      !! `whereis a2dissite` && system("a2dissite $base_name");

      httpd_restart();
    }
  } elseif ( ! cli::flag('force') && is_file($vhost_file)) {
    error("Already exists $vhost_file");
  } else {
    success("Writing $vhost_file");
    write($vhost_file, vhost_template());
    sleep(1);

    !! `whereis a2ensite` && system("a2ensite $base_name");

    httpd_restart();
  }
  update_hosts($base_name);
}

function vhost_template() {
  $base_path = CWD;
  $base_name = basename($base_path);
  $doc_root  = "$base_path/public";

  return <<<XML
<VirtualHost *:80>
  ServerName   $base_name.dev
  DocumentRoot $doc_root
  <Directory $doc_root/>
    Options -Indexes FollowSymLinks MultiViews
    AllowOverride All
    Order allow,deny
    allow from all
  </Directory>
  ErrorLog  $base_path/logs/error.log
  CustomLog $base_path/logs/access.log combined
</VirtualHost>
XML;
}

function update_hosts($base_name) {
  info('Verifying hosts file');

  $hosts_file = '/etc/hosts';
  $config     = read($hosts_file);
  $text       = "127.0.0.1\t$base_name.dev ##\n";


  if (cli::flag('force remove')) {
    $config = str_replace($text, '', $config);
    $config = str_replace($base_name, '', $config);
  }

  $modified = filesize($hosts_file) <> strlen($config);

  if (( ! cli::flag('remove') OR $modified) && ! strpos($config, $base_name)) {
    success("Updating $hosts_file");
    write($hosts_file, $config . (cli::flag('remove') ? '' : $text));
  } else {
    notice('Nothing to update');
  }
}

function httpd_restart() {
  $apache_bin = '/etc/init.d/apache2';

  ! is_file($apache_bin) && $apache_bin = 'apachectl';

  system("$apache_bin restart");
}
