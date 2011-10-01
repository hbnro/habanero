<?php

/**
 * Spanish app.generator strings
 */

$lang['missing_model_name'] = 'Hace falta el nombre del modelo';
$lang['missing_action_name'] = 'Hace falta el nombre de la acción';
$lang['missing_controller_name'] = 'Hace falta el nombre del controlador';

$lang['controller_already_exists'] = 'El controlador %{name} ya existe';
$lang['controller_missing'] = 'Hace falta un controlador';
$lang['controller_not_exists'] = 'El controlador %{name} no existe';
$lang['action_already_exists'] = 'La acción %{controller}#%{name} ya existe';

$lang['not_installed'] = 'No se ha instalado';

$lang['verifying_script'] = 'Verificando script';
$lang['verifying_generator'] = 'Comprobando generador';
$lang['verifying_installation'] = 'Comprobando instalación';

$lang['directory_must_be_empty'] = 'La carpeta de destino debe estar vacía';

$lang['counting_files'] = 'Cantidad de archivos: %{length}';
$lang['sizing_files'] = 'Peso total: %{size}';

$lang['action_method_building'] = 'Generando acción para %{controller}#%{name}';
$lang['action_route_building'] = 'Generando ruta para %{controller}#%{name}';
$lang['action_view_building'] = 'Generando vista para %{controller}#%{name}';

$lang['controller_class_building'] = 'Generando clase por defecto del controlador %{name}';
$lang['controller_route_building'] = 'Generando ruta por defecto del controlador %{name}';
$lang['controller_view_building'] = 'Generando vista por defecto del controlador %{name}';

$lang['model_already_exists'] = 'El modelo %{name} ya existe';
$lang['model_class_building'] = 'Generando clase del modelo %{name}';

$lang['missing_script_name'] = 'Hace falta el nombre del script';
$lang['missing_script_file'] = 'El script %{name} no existe';
$lang['missing_script_params'] = 'Hacen falta los parametros del script';
$lang['unknown_script_param'] = 'Parametro %{name} desconocido';

$lang['executing_script'] = 'Ejecutando %{name}';
$lang['configuration'] = 'Configuración';
$lang['application'] = 'Aplicación';
$lang['environment'] = 'Entorno: %{env}';

$lang['current_configuration'] = 'Configuración actual';
$lang['application_configuration'] = 'Configuración de la aplicación';
$lang['database_configuration'] = 'Configuración de la base de datos';
$lang['testing_configuration'] = 'Configuración del entorno de prueba';
$lang['development_configuration'] = 'Configuración del entorno de desarrollo';
$lang['production_configuration'] = 'Configuración del entorno de producción';
$lang['default_configuration'] = 'Configuración por defecto';

$lang['setting_application_options'] = 'Aplicando configuración de la aplicación';
$lang['setting_database_options'] = 'Aplicando configuración de la base de datos';
$lang['setting_testing_options'] = 'Aplicando configuración del entorno de prueba';
$lang['setting_development_options'] = 'Aplicando configuración del entorno de desarrollo';
$lang['setting_production_options'] = 'Aplicando configuración del entorno de producción';
$lang['setting_default_options'] = 'Aplicando configuración por defecto';

$lang['generator_intro'] = 'Generador de la aplicación';
$lang['generator_usage'] = <<<HELP

  \clight_gray(Muestra el estado actual de la aplicación.)\c
    \bgreen(app.st)\b

  \clight_gray(Genera y verifica la estructura de la aplicación.)\c
    \bgreen(app.gen)\b

  \clight_gray(Muestra y modifica las opciones de configuración.)\c
    \bgreen(app.conf)\b \byellow([--item=value])\b [...] [--global|dev|test|prod|app|db]

  \clight_gray(Generador del controlador por defecto.)\c
    \bgreen(app.make)\b \bcyan(controller)\b \byellow(name)\b [--view] [--helper] [--parent=class]

  \clight_gray(Generador de la acción por defecto.)\c
    \bgreen(app.make)\b \bcyan(action)\b \byellow(controller:name)\b [--view]

  \clight_gray(Generador del modelo por defecto.)\c
    \bgreen(app.make)\b \bcyan(model)\b \byellow(name[:table])\b [--parent=class]

  \clight_gray(Ejecuta tareas programadas.)\c
    \bgreen(app.run)\b \bcyan(script[:param])\b [...]

HELP;

/* EOF: ./cli/mods/app/locale/es.php */
