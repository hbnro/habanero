<?php

info("Verifying vhost availability");


$base_path = CWD;
$base_name = basename($base_path);

$test = array(
  '/etc/apache2/sites-available',
  '/private/etc/apache2/virtualhosts',
);

foreach ($test as $one) {
  if (is_dir($one)) {
    $vhost_path = $one;
    break;
  }
}

// TODO: two types of vhost-install -> via vhost-path/vhost-conf-file
// -> /private/etc/apache2/extra/httpd-vhosts.conf (Mac OS X 10.6)

if (empty($vhost_path)) {
  error('Not found a suitable vhost path on your system!');
} else {
  $vhost_file = "$vhost_path/$base_name";

  if (cli::flag('remove')) {
    if ( ! is_file($vhost_file)) {
      error("Not exists $vhost_file");
    } else {
      notice("Removing $vhost_file");
      unlink($vhost_file);

      sleep(1);


      !! `whereis a2dissite` && system("a2dissite $base_name");

      $apache_bin = '/etc/init.d/apache2';

      ! is_file($apache_bin) && $apache_bin = 'apachectl';

      system("$apache_bin restart");
    }
  } elseif ( ! cli::flag('force') && is_file($vhost_file)) {
    error("Already exists $vhost_file");
  } else {
    $doc_root = "$base_path/public";
    $scheme   = <<<XML
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


    write($vhost_file, $scheme);
    success("Writing $vhost_file");

    sleep(1);


    !! `whereis a2ensite` && system("a2ensite $base_name");

    $apache_bin = '/etc/init.d/apache2';

    ! is_file($apache_bin) && $apache_bin = 'apachectl';

    system("$apache_bin restart");
  }


  info('Verifying hosts file');

  $hosts_file = '/etc/hosts';
  $config     = trim(read($hosts_file)) . "\n";
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

bold('Done');
