<?php

/**
 * Application english strings
 */

$lang['controller_already_exists'] = 'Controller %{name} already exists';
$lang['controller_missing'] = 'Controller is missing';
$lang['controller_not_exists'] = 'Controller %{name} does not exists';
$lang['action_already_exists'] = 'Action %{controller}#%{name} already exists';
$lang['action_missing'] = 'Action is missing';

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

$lang['usage'] = <<<HELP

  \clight_gray(Display the current application status)\c
    \bgreen(app:status)\b

  \clight_gray(Generates and check the application structure)\c
    \bgreen(app:create)\b \bcyan(app)\b [--force]

  \clight_gray(Default controller generator)\c
    \bgreen(app:controller)\b \bcyan(name)\b [--parent=class] [--no-view]

  \clight_gray(Default action generator)\c
    \bgreen(app:action)\b \bcyan(controller:name)\b [--method=get|put|post|delete] [--route=X] [--path=Y] [--no-view]

HELP;

/* EOF: ./stack/scripts/application/locale/en.php */
