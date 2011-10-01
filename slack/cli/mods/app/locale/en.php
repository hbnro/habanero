<?php

/**
 * English app.generator strings
 */

$lang['missing_model_name'] = 'Model name missing';
$lang['missing_action_name'] = 'Action name missing';
$lang['missing_controller_name'] = 'Controller name missing';

$lang['controller_already_exists'] = 'Controller %{name} already exists';
$lang['controller_missing'] = 'Controller is missing';
$lang['controller_not_exists'] = 'Controller %{name} does not exists';
$lang['action_already_exists'] = 'Action %{controller}#%{name} already exists';

$lang['not_installed'] = 'Not installed';

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
$lang['missing_script_params'] = 'Missing script params';
$lang['unknown_script_param'] = 'Unknown param %{name}';

$lang['executing_script'] = 'Executing script %{name}';
$lang['configuration'] = 'Configuration';
$lang['application'] = 'Application';
$lang['environment'] = 'Environment: %{env}';

$lang['current_configuration'] = 'Loaded settings';
$lang['application_configuration'] = 'Application settings';
$lang['database_configuration'] = 'Database settings';
$lang['testing_configuration'] = 'Testing settings';
$lang['development_configuration'] = 'Development settings';
$lang['production_configuration'] = 'Production settings';
$lang['default_configuration'] = 'Default settings';

$lang['setting_application_options'] = 'Applying application configuration';
$lang['setting_database_options'] = 'Applying database configuration';
$lang['setting_testing_options'] = 'Applying testing configuration';
$lang['setting_development_options'] = 'Applying development configuration';
$lang['setting_production_options'] = 'Applying production configuration';
$lang['setting_default_options'] = 'Applying default configuration';

$lang['generator_intro'] = 'Application generator';
$lang['generator_usage'] = <<<HELP

  \clight_gray(Display the current application status.)\c
    \bgreen(app.st)\b

  \clight_gray(Generates and check the application structure.)\c
    \bgreen(app.gen)\b [--force]

  \clight_gray(Display and set the configuration options.)\c
    \bgreen(app.conf)\b \byellow([--item=value])\b [...] [--global|dev|test|prod|app|db]

  \clight_gray(Default controller generator.)\c
    \bgreen(app.make)\b \bcyan(controller)\b \byellow(name)\b [--view] [--helper] [--parent=class]

  \clight_gray(Default action generator.)\c
    \bgreen(app.make)\b \bcyan(action)\b \byellow(controller:name)\b [--view]

  \clight_gray(Default model generator.)\c
    \bgreen(app.make)\b \bcyan(model)\b \byellow(name[:table])\b [--parent=class]

  \clight_gray(Execute tasks.)\c
    \bgreen(app.run)\b \bcyan(script[:param])\b [...]

HELP;

/* EOF: ./cli/mods/app/locale/en.php */
