<?php

/**
 * A record english strings
 */

$lang['undefined_property'] = 'Undefined property %{class}#%{name}';
$lang['primary_key_missing'] = 'Primary key for %{model} is missing';

$lang['missing_model_name'] = 'Model name missing';
$lang['missing_model_file'] = 'Model %{name} does not exists';

$lang['model_already_exists'] = 'The model %{name} already exists';
$lang['model_class_building'] = 'Generating class for model %{name}';

$lang['verifying_import'] = 'Verifying import';
$lang['verifying_export'] = 'Verifying export';

$lang['import_model_missing'] = 'Import model is missing';
$lang['import_file_missing'] = 'Import file %{path} does not exists';

$lang['export_model_missing'] = 'Export model is missing';
$lang['export_already_exists'] = 'Export file already exists';
$lang['exporting'] = 'Exporting %{path}';
$lang['importing'] = 'Importing %{path}';

$lang['generator_title'] = 'Model generator';
$lang['generator_usage'] = <<<HELP

  \clight_gray(Default model generator)\c
    \bgreen(ar:model)\b \bcyan(name[:table])\b [--parent=class]

  \clight_gray(Manage the model data backups)\c
    \bgreen(ar:backup)\b \bcyan(model[:name])\b [--import] [--delete-all]

  \clight_gray(Open the a_record interactive console)\c
    \bgreen(ar:console)\b


HELP;

/* EOF: ./library/a_record/locale/en.php */
