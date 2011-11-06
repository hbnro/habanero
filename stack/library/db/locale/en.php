<?php

/**
 * English db strings
 */

$lang['pdo_adapter_missing'] = 'The PDO adapter does not exist: %{name}';
$lang['database_query_error'] = 'There was an error in the database: %{message} (%{sql})';
$lang['database_driver_missing'] = 'Invalid database driver: %{adapter}';
$lang['database_scheme_missing'] = 'Database scheme unavailable: %{adapter}';

$lang['migrating_database'] = 'Migrating database';

$lang['verifying_seed'] = 'Verifying records';
$lang['verifying_schema'] = 'Verifying schema';
$lang['verifying_import'] = 'Verifying import';
$lang['verifying_export'] = 'Verifying export';
$lang['verifying_database'] = 'Verifying database';
$lang['verifying_structure'] = 'Verifying structure';

$lang['model_class_building'] = 'Generating class for model %{name}';

$lang['column_already_exists'] = 'Column %{name} already exists';
$lang['column_name_missing'] = 'Column name is missing';
$lang['column_not_exists'] = 'Column %{table}.%{name} does not exists';

$lang['success_column_index'] = 'Index for %{table}.%{name} is valid';
$lang['success_field_type'] = 'Column type %{name}:%{type} is correct';
$lang['indexing_table'] = 'Adding index %{name} to %{table}';

$lang['column_type_missing'] = 'Column type is missing';
$lang['import_name_missing'] = 'Import name is missing';
$lang['import_file_missing'] = 'Import file %{path} does not exists';

$lang['index_already_exists'] = 'Index %{name} already exists';
$lang['index_columns_missing'] = 'Missing columns to index';
$lang['index_name_missing'] = 'Index name is missing';
$lang['index_not_exists'] = 'Index %{name} from table %{table} does not exists';

$lang['table_show_indexes'] = 'Showing indexes from %{name}';
$lang['table_already_exists'] = 'Table %{name} already exists';
$lang['renaming_table_to'] = 'Renaming table %{from} to %{to}';
$lang['table_fields_missing'] = 'Table fields for %{name} are missing';
$lang['table_name_missing'] = 'Table name is missing';
$lang['table_not_exists'] = 'Table %{name} does not exists';
$lang['table_pk_missing'] = 'Primary key for %{name} is missing';

$lang['without_indexes'] = 'There are no indexes for %{name}';
$lang['without_migrations'] = 'There are no migrations';
$lang['without_tables'] = 'There are no tables';
$lang['without_seed'] = 'There are no data';

$lang['loading_seed'] = 'Loading data from %{path}';
$lang['loading_schema'] = 'Loading schema from %{path}';
$lang['updating_schema'] = 'Updating schema %{path}';
$lang['unknown_field'] = 'Unknown type %{type} for column %{name}';

$lang['column_building'] = 'Creating column %{name}:%{type}';
$lang['column_changing'] = 'Changing column %{name}:%{type}';
$lang['column_dropping'] = 'Dropping column %{name}';
$lang['column_renaming'] = 'Renaming column %{from} to %{to}';

$lang['index_dropping'] = 'Dropping index %{name}';
$lang['run_migration'] = 'Executing migration %{path}';

$lang['table_column_indexing'] = 'Creating indexes for %{name}';
$lang['table_building'] = 'Creating table %{name}';
$lang['table_dropping'] = 'Dropping table %{name}';
$lang['table_show_columns'] = 'Showing columns from %{name}';

$lang['export_name_missing'] = 'Export name is missing';
$lang['export_already_exists'] = 'Export file already exists';
$lang['exporting'] = 'Exporting %{path}';
$lang['importing'] = 'Importing %{path}';

$lang['tables'] = 'Tables';

$lang['generator_usage'] = <<<HELP
  ====================
   Database generator
  ====================

  \clight_gray(Check the current database status)\c
    \bgreen(db:status)\b

  \clight_gray(Show the structure from specified table)\c
    \bgreen(db:show_table)\b \bcyan(table)\b

  \clight_gray(Delete the specified table)\c
    \bgreen(db:drop_table)\b \bcyan(table)\b

  \clight_gray(Rename the specified table)\c
    \bgreen(db:rename_table)\b \bcyan(table)\b \bwhite(new)\b

  \clight_gray(Create a table in the database)\c
    \bgreen(db:create_table)\b \bcyan(table)\b \byellow(field:type[:length])\b [...] [--model]

  \clight_gray(Adds a column to the specified table)\c
    \bgreen(db:add_column)\b \bcyan(table)\b \byellow(field:type[:length])\b [...]

  \clight_gray(Remove a column from specified table)\c
    \bgreen(db:remove_column)\b \bcyan(table)\b \byellow(name)\b [...]

  \clight_gray(Rename a column form specified table)\c
    \bgreen(db:rename_column)\b \bcyan(table)\b \byellow(name)\b \bwhite(new)\b [...]

  \clight_gray(Change the column definition from specified table)\c
    \bgreen(db:change_column)\b \bcyan(table)\b \byellow(name)\b \bwhite(type[:length])\b [...]

  \clight_gray(Adds a index to the specified table)\c
    \bgreen(db:add_index)\b \bcyan(table)\b \byellow(name)\b \bwhite(column)\b [...] [--unique]

  \clight_gray(Remove a index from the specified table)\c
    \bgreen(db:remove_index)\b \bcyan(table)\b \byellow(name)\b

  \clight_gray(Manage the database backups)\c
    \bgreen(db:backup)\b \bcyan(name)\b [--raw] [--data] [--import]

  \clight_gray(Run migrations)\c
    \bgreen(db:migrate)\b [--drop-all] [--schema] [--seed]


HELP;

/* EOF: ./stack/library/db/locale/en.php */
