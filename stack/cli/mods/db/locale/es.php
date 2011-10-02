<?php

/**
 * Spanish db.generator strings
 */

$lang['migrating_database'] = 'Migrando base de datos';

$lang['verifying_seed'] = 'Comprobando registros';
$lang['verifying_schema'] = 'Comprobando esquema';
$lang['verifying_import'] = 'Comprobando importación';
$lang['verifying_export'] = 'Comprobando exportación';
$lang['verifying_database'] = 'Comprobando base de datos';
$lang['verifying_structure'] = 'Comprobando estructura';

$lang['model_class_building'] = 'Generando clase del modelo %{name}';

$lang['column_already_exists'] = 'La columna %{name} ya existe';
$lang['column_name_missing'] = 'Hace falta el nombre de la columna';
$lang['column_not_exists'] = 'La columna %{table}.%{name} no existe';

$lang['success_column_index'] = 'El índice %{table}.%{name} es correcto';
$lang['success_field_type'] = 'El tipo de columna %{name}:%{type} es correcto';
$lang['indexing_table'] = 'Agregando indice %{name} a la tabla %{table}';

$lang['column_type_missing'] = 'Hace falta el tipo de columna';
$lang['import_name_missing'] = 'Hace falta el nombre para importar';
$lang['import_file_missing'] = 'El archivo a importar %{path} no existe';

$lang['index_already_exists'] = 'El índice %{name} ya existe';
$lang['index_columns_missing'] = 'Hace falta una columna';
$lang['index_name_missing'] = 'Hace falta el nombre del índice';
$lang['index_not_exists'] = 'El índice %{name} de la tabla %{table} no existe';

$lang['table_show_indexes'] = 'Mostrando indices de %{name}';
$lang['table_already_exists'] = 'La tabla %{name} ya existe';
$lang['renaming_table_to'] = 'Renombrando tabla de %{from} a %{to}';
$lang['table_fields_missing'] = 'Hacen falta las columnas para %{name}';
$lang['table_name_missing'] = 'Hace falta el nombre de la tabla';
$lang['table_not_exists'] = 'La tabla %{name} no existe';
$lang['table_pk_missing'] = 'Falta la clave primaria de %{name}';

$lang['without_indexes'] = 'La tabla %{name} no tiene indices';
$lang['without_migrations'] = 'Sin migraciones';
$lang['without_tables'] = 'Sin tablas';
$lang['without_seed'] = 'Sin datos';

$lang['loading_seed'] = 'Cargando datos de %{path}';
$lang['loading_schema'] = 'Cargando esquema de %{path}';
$lang['unknown_field'] = 'No se conoce el tipo %{type} de la columna %{name}';

$lang['column_building'] = 'Creando columna %{name}:%{type}';
$lang['column_changing'] = 'Cambiando columna %{name}:%{type}';
$lang['column_dropping'] = 'Eliminando columna %{name}';
$lang['column_renaming'] = 'Renombrando columna de %{from} a %{to}';

$lang['index_dropping'] = 'Borrando índice %{name}';
$lang['run_migration'] = 'Ejecutando migración %{path}';

$lang['table_column_indexing'] = 'Creando indices en %{name}';
$lang['table_building'] = 'Creando tabla %{name}';
$lang['table_dropping'] = 'Eliminando tabla %{name}';
$lang['table_show_columns'] = 'Mostrando columnas de %{name}';

$lang['export_name_missing'] = 'Hace falta un nombre para exportar';
$lang['export_already_exists'] = 'El archivo a exportar ya existe';
$lang['exporting'] = 'Exportando %{path}';
$lang['importing'] = 'Importando %{path}';

$lang['tables'] = 'Tablas';

$lang['generator_intro'] = 'Generador de la base de datos';
$lang['generator_usage'] = <<<HELP

  \clight_gray(Estado de la base de datos)\c
    \bgreen(db.st)\b

  \clight_gray(Estructura de la tabla especificada)\c
    \bgreen(db.show)\b \bcyan(table)\b

  \clight_gray(Borra la tabla especificada)\c
    \bgreen(db.drop)\b \bcyan(table)\b

  \clight_gray(Renombra la tabla especificada)\c
    \bgreen(db.rename)\b \bcyan(table)\b \bwhite(new)\b

  \clight_gray(Crea una tabla en la base de datos)\c
    \bgreen(db.create)\b \bcyan(table)\b \byellow(field:type[:length])\b [...] [--model]

  \clight_gray(Agrega una columna a la table especificada)\c
    \bgreen(db.add_column)\b \bcyan(table)\b \byellow(field:type[:length])\b [...]

  \clight_gray(Elimina una columna de la tabla especificada)\c
    \bgreen(db.remove_column)\b \bcyan(table)\b \byellow(name)\b [...]

  \clight_gray(Renombra una columna de la tabla especificada)\c
    \bgreen(db.rename_column)\b \bcyan(table)\b \byellow(name)\b \bwhite(new)\b [...]

  \clight_gray(Cambia la definición de una columna en la tabla especificada)\c
    \bgreen(db.change_column)\b \bcyan(table)\b \byellow(name)\b \bwhite(type[:length])\b [...]

  \clight_gray(Agrega un índice a la tabla especificada)\c
    \bgreen(db.add_index)\b \bcyan(table)\b \byellow(name)\b \bwhite(column)\b [...] [--unique]

  \clight_gray(Elimina un índice de la tabla especificada)\c
    \bgreen(db.remove_index)\b \bcyan(table)\b \byellow(name)\b

  \clight_gray(Copias de seguridad de la base de datos)\c
    \bgreen(db.backup)\b \bcyan(name)\b [--raw] [--data] [--import]

  \clight_gray(Ejecuta las migraciones)\c
    \bgreen(db.make)\b [--drop-all] [--seed]

HELP;

/* EOF: ./cli/mods/db/locale/es.php */
