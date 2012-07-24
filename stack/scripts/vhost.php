<?php

info(ln('verifying_vhosts'));

if (IS_WIN) {
  success(vhost_template());
} else {
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
    error(ln('missing_vhost_conf'));
  } else {
    is_dir($vhost_path) ? vhost_create($vhost_path) : vhost_write($vhost_path);
  }
}

done();


function vhost_write($vhost_path) {
  $base_path  = APP_PATH;
  $base_name  = basename($base_path);
  $config     = read($vhost_path);

  if (cli::flag('remove')) {
    if ( ! strpos($config, "$base_name.dev")) {
      error(ln('vhost_not_found', array('name' => "$base_name.dev")));
    } else {
      notice(ln('vhost_remove', array('name' => "$base_name.dev")));
      $old_vhost = "<(VirtualHost)[^<>]+>[^<>]+$base_name.dev[^<>]+<(Directory)[^<>]+>[^<>]+<\/\\2>[^<>]*<\/\\1>";
      write($vhost_path, preg_replace("/\s*$old_vhost/is", "\n", $config));
      httpd_restart();
    }
  } elseif (strpos($config, "$base_name.dev")) {
    error(ln('vhost_exists', array('name' => "$base_name.dev")));
  } else {
    success(ln('vhost_append', array('name' => "$base_name.dev")));
    write($vhost_path, "$config\n" . vhost_template());
    httpd_restart(TRUE);
  }
  update_hosts($base_name);
}

function vhost_create($vhost_path) {
  $base_path  = APP_PATH;
  $base_name  = basename($base_path);
  $vhost_file = "$vhost_path/$base_name";

  if (cli::flag('remove')) {
    if ( ! is_file($vhost_file)) {
      error(ln('vhost_not_found', array('name' => $vhost_file)));
    } else {
      notice(ln('vhost_remove', array('name' => $vhost_file)));
      unlink($vhost_file);
      httpd_restart();
    }
  } elseif (is_file($vhost_file)) {
    error(ln('vhost_exists', array('name' => $vhost_file)));
  } else {
    success(ln('vhost_write', array('name' => $vhost_file)));
    write($vhost_file, vhost_template());
    httpd_restart(TRUE);
  }
  update_hosts($base_name);
}

function vhost_template() {
  $base_path = APP_PATH;
  $base_name = basename($base_path);

  return <<<XML
<VirtualHost *:80>
  ServerName   $base_name.dev
  DocumentRoot "$base_path"
  AccessFileName .develop
  <Directory "$base_path/">
    Options -Indexes FollowSymLinks MultiViews
    AllowOverride All
    Order allow,deny
    allow from all
  </Directory>
  ErrorLog  "$base_path/logs/error.log"
  CustomLog "$base_path/logs/access.log" combined
</VirtualHost>
XML;
}

function update_hosts($base_name) {
  info(ln('verify_hosts'));

  $hosts_file = '/etc/hosts';
  $config     = read($hosts_file);
  $text       = "127.0.0.1\t$base_name.dev ##\n";


  if (cli::flag('remove')) {
    $config = str_replace($text, '', $config);
    $config = str_replace("$base_name.dev", '', $config);
  }

  $modified = filesize($hosts_file) <> strlen($config);

  if (( ! cli::flag('remove') OR $modified) && ! strpos($config, $base_name)) {
    success(ln('update_hosts', array('name' => $hosts_file)));
    write($hosts_file, $config . (cli::flag('remove') ? '' : $text));
  } else {
    notice(ln('update_nothing'));
  }
}

function httpd_restart($enable = FALSE) {
  sleep(1);

  $base_name  = basename(APP_PATH);

  $apache_bin = '/etc/init.d/apache2';

  $site_cmd   = $enable ? 'a2ensite' : 'a2dissite';


  !! `whereis $site_cmd` && system("$site_cmd $base_name");

  ! is_file($apache_bin) && $apache_bin = 'apachectl';

  system("$apache_bin restart");
}

/* EOF: ./stack/scripts/vhost.php */
