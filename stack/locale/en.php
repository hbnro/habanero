<?php

/**
 * English generator strings
 */

$lang['generator_intro'] = <<<INTRO

  Welcome to the \blight_gray,black(atl)\b console utility!

  Usage:
    atl \bgreen(<command>)\b [arguments] [...]

  Extras:
    --run \bcyan(script[:param])\b    \cdark_gray(*)\c Execute tasks and scripts
    --task \clight_gray([--php])\c \bcyan(name)\b     \cdark_gray(*)\c Generate scripts/tasks for the application
           \clight_gray([command] [...])\c
    --config \bcyan([--item=value])\b \cdark_gray(*)\c Display and set the configuration options
             \clight_gray([...] [--global|app|dev|prod])\c

    --install               \cyellow(*)\c Configure the framework
    --uninstall             \cyellow(*)\c Remove the framework configuration
    --vhost \bred([--remove])\b      \cyellow(*)\c Create or remove virtual hosts in the system

    --open                  \cdark_gray(*)\c Launch the default browser with virtual host domain
    --stub                  \cdark_gray(*)\c Make a local copy from the system libraries
    --help                  \cdark_gray(*)\c Display the descriptions of all generators

    \cyellow(* needs sudo permissions)\c

  Examples:
    \bwhite(sudo)\b atl --vhost
    atl --run rsync:deploy
    atl --config --global --language=es


INTRO;

$lang['current_configuration'] = 'Loaded settings';
$lang['application_configuration'] = 'Application settings';
$lang['development_configuration'] = 'Development settings';
$lang['production_configuration'] = 'Production settings';
$lang['default_configuration'] = 'Default settings';

$lang['setting_application_options'] = 'Applying application configuration';
$lang['setting_development_options'] = 'Applying development configuration';
$lang['setting_production_options'] = 'Applying production configuration';
$lang['setting_default_options'] = 'Applying default configuration';

$lang['missing_script_name'] = 'Missing script name';
$lang['missing_script_file'] = 'Missing script file %{name}';
$lang['missing_task_namespace'] = 'Missing task %{namespace}';
$lang['unknown_task_command'] = 'Unknown task %{command}';

$lang['executing_script'] = 'Executing %{path}';
$lang['executing_task'] = 'Executing task %{command}';
$lang['available_tasks'] = 'Available tasks';
$lang['verifying_script'] = 'Verifying script';

$lang['verifying_namespace'] = 'Verifying script %{name}';
$lang['creating_script'] = 'Creating script %{name}';
$lang['creating_task'] = 'Creating task %{command}';
$lang['script_exists'] = 'The script %{name} already exists';
$lang['task_exists'] = 'The task %{command} already exists';

$lang['missing_arguments'] = 'Missing arguments';
$lang['undefined_cmd'] = 'Undefined %{name} command';
$lang['search_php_ini'] = 'Looking for php configuration';
$lang['missing_php_ini'] = 'Not found a suitable php.ini file on your system!';
$lang['update_include_path'] = 'Updating include_path';
$lang['without_changes'] = 'Without changes';
$lang['launch_vhost'] = 'Launching vhost';
$lang['compressing_files'] = 'Compressing files...';
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
