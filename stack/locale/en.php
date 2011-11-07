<?php

/**
 * English generator strings
 */

$lang['generator_intro'] = <<<INTRO

  ¡Welcome to the \bwhite(atl)\b console utility!

  Usage:
    atl \bgreen(<command>)\b [arguments] [...]

  Extras:
    --install           \cyellow(*)\c Configure the framework
    --uninstall         \cyellow(*)\c Remove the framework configuration
    --vhost [--remove]  \cyellow(*)\c Create or remove virtual hosts in the system
    --open              \cdark_gray(*)\c Launch the default browser with virtual host domain
    --stub              \cdark_gray(*)\c Make a local copy from the system libraries
    --help              \cdark_gray(*)\c Display the descriptions of all generators

    \cyellow(* needs sudo permissions)\c

  Example:
    \bwhite(sudo)\b atl --vhost


INTRO;

$lang['missing_arguments'] = 'Missing arguments';
$lang['undefined_cmd'] = 'Undefined %{name} command';
$lang['search_php_ini'] = 'Looking for php configuration';
$lang['missing_php_ini'] = 'Not found a suitable php.ini file on your system!';
$lang['update_include_path'] = 'Updating include_path';
$lang['without_changes'] = 'Without changes';
$lang['launch_vhost'] = 'Launching vhost';
$lang['copying_libraries'] = 'Copying entire libraries';
$lang['copying_stub_path'] = 'Copying %{name} into %{path}';
$lang['verifying_vhosts'] = 'Verifying vhost availability';
$lang['missing_vhost_conf'] = 'Not found a suitable vhost configuration on your system!';
$lang['vhost_not_found'] = 'Not found %{name} vhost';
$lang['vhost_remove'] = 'Removing vhost %{name}';
$lang['vhost_exists'] = 'Already exists %{name} vhost';
$lang['vhost_append'] = 'Appending %{name} vhost';
$lang['vhost_write'] = 'Writing %{name} vhost';
$lang['verify_hosts'] = 'Verifying hosts file';
$lang['update_hosts'] = 'Updating %{name}';
$lang['update_nothing'] = 'Nothing to update';
$lang['done'] = 'Done';

/* EOF: ./stack/locale/en.php */
