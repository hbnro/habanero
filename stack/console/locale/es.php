<?php

/**
 * Spanish generator strings
 */

$lang['generator_intro'] = <<<INTRO
  ¡Bienvenido a la utilidad de consola \bwhite(atl)\b!

  Uso:
    atl \bgreen(<comando>)\b [argumentos] [...]

  Extras (con permisos de sudo):
    --install           \cdark_gray(#)\c Configura el framework
    --uninstall         \cdark_gray(#)\c Elimina la configuración del framework
    --vhost [--remove]  \cdark_gray(#)\c Crea o elimina un host virtual en el sistema

  Ejemplo:
    \bwhite(sudo)\b atl --vhost


INTRO;

$lang['missing_arguments'] = 'Hacen falta argumentos';
$lang['undefined_cmd'] = 'El comando %{name} no está definido';
$lang['search_php_ini'] = 'Buscando la configuración de php';
$lang['missing_php_ini'] = 'No se encontró el archivo php.ini en el sistema!';
$lang['update_include_path'] = 'Actualizando include_path';
$lang['without_changes'] = 'Sin cambios';
$lang['launch_vhost'] = 'Lanzando vhost';
$lang['copying_libraries'] = 'Copiando las librerías';
$lang['copying_stub_path'] = 'Copiando /%{name} en %{path}';
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

/* EOF: ./stack/console/locale/es.php */
