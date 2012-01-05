<?php

/**
 * A record spanish strings
 */

$lang['undefined_property'] = 'La propiedad %{class}#%{name} no está definida';
$lang['primary_key_missing'] = 'La clave primaria de %{model} no existe';

$lang['missing_model_name'] = 'Hace falta el nombre del modelo';

$lang['model_already_exists'] = 'El modelo %{name} ya existe';
$lang['model_class_building'] = 'Generando clase del modelo %{name}';

$lang['verifying_import'] = 'Comprobando importación';
$lang['verifying_export'] = 'Comprobando exportación';

$lang['import_name_missing'] = 'Hace falta el nombre para importar';
$lang['import_file_missing'] = 'El archivo a importar %{path} no existe';

$lang['export_name_missing'] = 'Hace falta un nombre para exportar';
$lang['export_already_exists'] = 'El archivo a exportar ya existe';
$lang['exporting'] = 'Exportando %{path}';
$lang['importing'] = 'Importando %{path}';

$lang['generator_title'] = 'Generador de modelo';
$lang['generator_usage'] = <<<HELP

  \clight_gray(Generador del modelo por defecto)\c
    \bgreen(ar:model)\b \bcyan(name[:table])\b [--parent=class]

  \clight_gray(Copias de seguridad de los modelos de datos)\c
    \bgreen(ar:backup)\b \bcyan(model[:name])\b [--import] [--replace]

  \clight_gray(Abre la consola interactiva de a_record)\c
    \bgreen(ar:console)\b


HELP;

/* EOF: ./library/a_record/locale/es.php */
