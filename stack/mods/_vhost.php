<?php

info('Verifying vhost availability');

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
  $base_name  = basename($base_path);
  $config     = read($vhost_path);

  if (cli::flag('remove')) {
    if ( ! strpos($config, "$base_name.dev")) {
      error("Not found $base_name.dev");
    } else {
      notice("Removing $base_name.dev");
      $old_vhost = "<(VirtualHost)[^<>]+>[^<>]+$base_name.dev[^<>]+<(Directory)[^<>]+>[^<>]+<\/\\2>[^<>]*<\/\\1>";
      write($vhost_path, preg_replace("/\s*$old_vhost/is", "\n", $config));
      httpd_restart();
    }
  } elseif (strpos($config, "$base_name.dev")) {
    error("Already exists $base_name.dev");
  } else {
    success("Appending $base_name.dev");
    write($vhost_path, "$config\n" . vhost_template());
    httpd_restart(TRUE);
  }
  update_hosts($base_name);
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
      httpd_restart();
    }
  } elseif (is_file($vhost_file)) {
    error("Already exists $vhost_file");
  } else {
    success("Writing $vhost_file");
    write($vhost_file, vhost_template());
    httpd_restart(TRUE);
  }
  update_hosts($base_name);
}

function vhost_template() {
  $base_path = CWD;
  $base_name = basename($base_path);

  $docs_root = "$base_path/public";
  $logs_path = "$base_path/logs";

  ! is_dir($docs_root) && $docs_root = $base_path;
  ! is_dir($logs_path) && $logs_path = $base_path;


  return <<<XML
<VirtualHost *:80>
  ServerName   $base_name.dev
  DocumentRoot "$docs_root"
  <Directory "$docs_root/">
    Options -Indexes FollowSymLinks MultiViews
    AllowOverride All
    Order allow,deny
    allow from all
  </Directory>
  ErrorLog  "$logs_path/error.log"
  CustomLog "$logs_path/access.log" combined
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
    $config = str_replace("$base_name.dev", '', $config);
  }

  $modified = filesize($hosts_file) <> strlen($config);

  if (( ! cli::flag('remove') OR $modified) && ! strpos($config, $base_name)) {
    success("Updating $hosts_file");
    write($hosts_file, $config . (cli::flag('remove') ? '' : $text));
  } else {
    notice('Nothing to update');
  }
}

function httpd_restart($enable = FALSE) {
  sleep(1);

  $apache_bin = '/etc/init.d/apache2';

  $site_cmd   = $enable ? 'a2ensite' : 'a2dissite';


  !! `whereis $site_cmd` && system("$site_cmd $base_name");

  ! is_file($apache_bin) && $apache_bin = 'apachectl';

  system("$apache_bin restart");
}
