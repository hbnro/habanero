<?php

/**
 * Application spanish strings
 */

$lang['controller_missing'] = 'No existe el controlador %{name}';
$lang['action_missing'] = 'No existe la acción %{controller}#%{action}';

$lang['missing_action_name'] = 'Hace falta el nombre de la acción';
$lang['missing_controller_name'] = 'Hace falta el nombre del controlador';

$lang['controller_already_exists'] = 'El controlador %{name} ya existe';
$lang['controller_missing'] = 'Hace falta un controlador';
$lang['controller_not_exists'] = 'El controlador %{name} no existe';
$lang['action_already_exists'] = 'La acción %{controller}#%{name} ya existe';

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

$lang['missing_script_name'] = 'Hace falta el nombre del script';
$lang['missing_script_file'] = 'El script %{name} no existe';
$lang['missing_task_class'] = 'Hace falta una clase en %{path}';
$lang['unknown_task_param'] = 'Parametro desconocido %{name}';

$lang['executing_script'] = 'Ejecutando %{path}';
$lang['executing_task'] = 'Ejecutando tarea %{name}#%{param}';
$lang['configuration'] = 'Configuración';
$lang['application'] = 'Aplicación';

$lang['current_configuration'] = 'Configuración actual';
$lang['application_configuration'] = 'Configuración de la aplicación';
$lang['development_configuration'] = 'Configuración del entorno de desarrollo';
$lang['production_configuration'] = 'Configuración del entorno de producción';
$lang['default_configuration'] = 'Configuración por defecto';

$lang['setting_application_options'] = 'Aplicando configuración de la aplicación';
$lang['setting_development_options'] = 'Aplicando configuración del entorno de desarrollo';
$lang['setting_production_options'] = 'Aplicando configuración del entorno de producción';
$lang['setting_default_options'] = 'Aplicando configuración por defecto';

$lang['writting_asset'] = 'Escribiendo los recursos %{type}';
$lang['missing_asset'] = 'Hacen falta los recursos %{type}';

$lang['generator_title'] = 'Generador de la aplicación';
$lang['generator_usage'] = <<<HELP

  \clight_gray(Muestra el estado actual de la aplicación)\c
    \bgreen(app:status)\b

  \clight_gray(Genera y verifica la estructura de la aplicación)\c
    \bgreen(app:create)\b \bcyan(app)\b [--force]

  \clight_gray(Muestra y modifica las opciones de configuración)\c
    \bgreen(app:configure)\b \bcyan([--item=value])\b [...] [--global|app|dev|prod]

  \clight_gray(Generador del controlador por defecto)\c
    \bgreen(app:controller)\b \bcyan(name)\b [--view] [--helper] [--parent=class]

  \clight_gray(Generador de la acción por defecto)\c
    \bgreen(app:action)\b \bcyan(controller:name)\b [--view] [--method=get|put|post|delete]

  \clight_gray(Ejecuta tareas programadas)\c
    \bgreen(app:execute)\b \bcyan(script[:param])\b [...]

  \clight_gray(Genera los assets de la aplicación para producción)\c
    \bgreen(app:precompile)\b


HELP;

/* EOF: ./library/application/locale/es.php */
