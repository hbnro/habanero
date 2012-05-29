<?php

/**
 * Application english strings
 */

$lang['controller_missing'] = 'Controller %{name} missing';
$lang['action_missing'] = 'Controller action  %{controller}#%{action} missing';

$lang['missing_action_name'] = 'Action name missing';
$lang['missing_controller_name'] = 'Controller name missing';

$lang['controller_already_exists'] = 'Controller %{name} already exists';
$lang['controller_missing'] = 'Controller is missing';
$lang['controller_not_exists'] = 'Controller %{name} does not exists';
$lang['action_already_exists'] = 'Action %{controller}#%{name} already exists';

$lang['verifying_generator'] = 'Verifying generator';
$lang['verifying_installation'] = 'Verifying installation';

$lang['directory_must_be_empty'] = 'Directory must be empty';

$lang['counting_files'] = 'Number of files: %{length}';
$lang['sizing_files'] = 'Total weight: %{size}';

$lang['action_method_building'] = 'Generating action %{controller}#%{name}';
$lang['action_route_building'] = 'Generating route for %{controller}#%{name}';
$lang['action_view_building'] = 'Generating view for %{controller}#%{name}';

$lang['controller_class_building'] = 'Generating default class for controller %{name}';
$lang['controller_route_building'] = 'Generating route for default controller %{name}';
$lang['controller_view_building'] = 'Generating view for default controller %{name}';

$lang['compiling_asset'] = 'Compiling file %{name}';
$lang['appending_asset'] = 'Appending file %{name}';

$lang['generator_title'] = 'Application generator';
$lang['generator_usage'] = <<<HELP

  \clight_gray(Display the current application status)\c
    \bgreen(app:status)\b

  \clight_gray(Generates and check the application structure)\c
    \bgreen(app:create)\b \bcyan(app)\b [--force]

  \clight_gray(Default controller generator)\c
    \bgreen(app:controller)\b \bcyan(name)\b [--view] [--helper] [--parent=class]

  \clight_gray(Default action generator)\c
    \bgreen(app:action)\b \bcyan(controller:name)\b [--view] [--method=get|put|post|delete]

  \clight_gray(Build application assets to production)\c
    \bgreen(app:prepare)\b


HELP;

/* EOF: ./library/application/locale/en.php */
