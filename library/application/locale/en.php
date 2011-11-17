<?php

/**
 * Application english strings
 */

$lang['controller_missing'] = 'Controller %{name} missing';
$lang['action_missing'] = 'Controller action  %{controller}#%{action} missing';

$lang['missing_model_name'] = 'Model name missing';
$lang['missing_action_name'] = 'Action name missing';
$lang['missing_controller_name'] = 'Controller name missing';

$lang['controller_already_exists'] = 'Controller %{name} already exists';
$lang['controller_missing'] = 'Controller is missing';
$lang['controller_not_exists'] = 'Controller %{name} does not exists';
$lang['action_already_exists'] = 'Action %{controller}#%{name} already exists';

$lang['verifying_script'] = 'Verifying script';
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

$lang['model_already_exists'] = 'The model %{name} already exists';
$lang['model_class_building'] = 'Generating class for model %{name}';

$lang['missing_script_name'] = 'Missing script name';
$lang['missing_script_file'] = 'Missing script file %{name}';
$lang['missing_task_class'] = 'Missing task class %{path}';
$lang['unknown_task_param'] = 'Unknown task param %{name}';

$lang['executing_script'] = 'Executing %{path}';
$lang['executing_task'] = 'Executing task %{name}#%{param}';
$lang['configuration'] = 'Configuration';
$lang['application'] = 'Application';

$lang['current_configuration'] = 'Loaded settings';
$lang['application_configuration'] = 'Application settings';
$lang['development_configuration'] = 'Development settings';
$lang['production_configuration'] = 'Production settings';
$lang['default_configuration'] = 'Default settings';

$lang['setting_application_options'] = 'Applying application configuration';
$lang['setting_development_options'] = 'Applying development configuration';
$lang['setting_production_options'] = 'Applying production configuration';
$lang['setting_default_options'] = 'Applying default configuration';

$lang['generator_title'] = 'Application generator';
$lang['generator_usage'] = <<<HELP

  \clight_gray(Display the current application status)\c
    \bgreen(status)\b

  \clight_gray(Generates and check the application structure)\c
    \bgreen(create)\b \byellow(app)\b [--force]

  \clight_gray(Display and set the configuration options)\c
    \bgreen(configure)\b \byellow([--item=value])\b [...] [--global|app|dev|prod]

  \clight_gray(Default controller generator)\c
    \bgreen(generate)\b \bcyan(controller)\b \byellow(name)\b [--view] [--helper] [--parent=class]

  \clight_gray(Default action generator)\c
    \bgreen(generate)\b \bcyan(action)\b \byellow(controller:name)\b [--view]

  \clight_gray(Default model generator)\c
    \bgreen(generate)\b \bcyan(model)\b \byellow(name[:table])\b [--parent=class]

  \clight_gray(Execute tasks)\c
    \bgreen(execute)\b \bcyan(script[:param])\b [...]

  \clight_gray(Build application assets to production)\c
    \bgreen(precompile)\b


HELP;

/* EOF: ./library/application/locale/en.php */
