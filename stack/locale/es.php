<?php

/**
 * Spanish generator strings
 */

$lang['welcome'] = <<<INTRO

  ¡Bienvenido a la utilidad de consola \blight_gray,black(atl)\b!

  Uso:
    atl \bgreen(<comando>)\b [argumentos] [...]

  Extras:
    --run \bcyan(script[:param])\b      Ejecuta tareas programadas
    --task \clight_gray([--php])\c \bcyan(name)\b       Genera scripts/tareas para la aplicación
           \clight_gray([command] [...])\c
    --config \bcyan([--item=value])\b   Muestra y modifica las opciones de configuración
             \clight_gray([...] [--global|app|dev|prod])\c

    --install               \cyellow(*)\c Configura el include_path de php
    --uninstall             \cyellow(*)\c Elimina la configuración del include_path
    --vhost \bred([--remove])\b      \cyellow(*)\c Crea o elimina un host virtual en el sistema

    --open                    Abre el host virtual en el navegador por defecto
    --stub \clight_gray([--no-compress])\c    Crea una copia local con las librerías del sistema
    --help                    Muestra la descripción de uso de los generadores

    \cyellow(* requiere permisos de sudo)\c

  Ejemplos:
    \bwhite(sudo)\b atl --vhost
    atl --run rsync:deploy
    atl --config --global --language=en
    atl assets:prepare
    atl app:create blog
    atl db:create_table post title:string body:text --timestamps
    atl ar:model post


INTRO;

$lang['current_configuration'] = 'Configuración actual';
$lang['application_configuration'] = 'Configuración de la aplicación';
$lang['development_configuration'] = 'Configuración del entorno de desarrollo';
$lang['production_configuration'] = 'Configuración del entorno de producción';
$lang['default_configuration'] = 'Configuración por defecto';

$lang['setting_application_options'] = 'Aplicando configuración de la aplicación';
$lang['setting_development_options'] = 'Aplicando configuración del entorno de desarrollo';
$lang['setting_production_options'] = 'Aplicando configuración del entorno de producción';
$lang['setting_default_options'] = 'Aplicando configuración por defecto';

$lang['missing_script_name'] = 'Hace falta el nombre del script';
$lang['missing_script_file'] = 'El script %{name} no existe';
$lang['missing_task_namespace'] = 'Hace falta la tarea %{namespace}';
$lang['unknown_task_command'] = 'Tarea desconocida %{command}';

$lang['executing_script'] = 'Ejecutando %{path}';
$lang['executing_task'] = 'Ejecutando tarea %{command}';
$lang['available_tasks'] = 'Tareas disponibles';
$lang['verifying_script'] = 'Verificando script';

$lang['verifying_namespace'] = 'Verificando script %{name}';
$lang['creating_script'] = 'Creando script %{name}';
$lang['creating_task'] = 'Creando tarea %{command}';
$lang['script_exists'] = 'El script %{name} ya existe';
$lang['task_exists'] = 'La tarea %{command} ya existe';

$lang['missing_arguments'] = 'Hacen falta argumentos';
$lang['undefined_cmd'] = 'El comando %{name} no está definido';
$lang['search_php_ini'] = 'Buscando la configuración de php';
$lang['missing_php_ini'] = 'No se encontró el archivo php.ini en el sistema!';
$lang['update_include_path'] = 'Actualizando include_path';
$lang['without_changes'] = 'Sin cambios';
$lang['launch_vhost'] = 'Lanzando vhost';
$lang['compressing_files'] = 'Comprimiendo scripts...';
$lang['copying_libraries'] = 'Copiando las librerías';
$lang['copying_stub_path'] = 'Copiando %{name} en %{path}';
$lang['verifying_vhosts'] = 'Comprobando la disponibilidad del vhost';
$lang['missing_vhost_conf'] = 'No se encontró la configuración de vhosts en el sistema!';
$lang['vhost_not_found'] = 'El vhost %{name} no existe';
$lang['vhost_remove'] = 'Eliminando el vhost %{name}';
$lang['vhost_exists'] = 'El vhost %{name} ya existe';
$lang['vhost_append'] = 'Agregando el vhost %{name}';
$lang['vhost_write'] = 'Escribiendo el vhost %{name}';
$lang['verify_hosts'] = 'Verificando el archivo de hosts';
$lang['update_hosts'] = 'Actualizando %{name}';
$lang['update_nothing'] = 'Nada que actualizar';
$lang['done'] = 'Hecho';

/* EOF: ./stack/locale/es.php */
