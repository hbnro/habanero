<?php

/**
 * A record english strings
 */

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

$lang['verifying_structure'] = 'Verifying structure';
$lang['unknown_field'] = 'Unknown field %{name}';
$lang['unknown_field_type'] = 'Unknown field type %{name}:%{type}';
$lang['missing_fields'] = 'Missing fields';
$lang['crud_already_exists'] = 'CRUD for %{name} already exists';

$lang['a_record_created'] = 'The record was created';
$lang['a_record_updated'] = 'The record was updated';
$lang['a_record_deleted'] = 'The record was deleted';
$lang['a_record_missing'] = 'The record was not found';
$lang['a_record_set_deleted'] = 'The selected records was deleted';
$lang['a_record_set_missing'] = 'Select at least one record';
$lang['a_record_required_field'] = 'The field %{name} is required';

$lang['cancel'] = 'Cancel';
$lang['create_record'] = 'Create %{name}';
$lang['new_record'] = 'New %{name}';
$lang['confirm_delete'] = 'Are you sure?';
$lang['confirm_delete_all'] = 'Are you sure of all?';
$lang['delete_selected'] = 'Delete selected %{name}s';
$lang['update_record'] = 'Update %{name}';
$lang['delete'] = 'Delete';
$lang['edit'] = 'Edit';

$lang['usage'] = <<<HELP

  \clight_gray(Default model generator)\c
    \bgreen(ar:model)\b \bcyan(name[:table])\b [--parent=class]

  \clight_gray(Manage the model data backups)\c
    \bgreen(ar:backup)\b \bcyan(model[:name])\b [--import] [--delete-all]

  \clight_gray(Open the a_record interactive console)\c
    \bgreen(ar:console)\b

  \clight_gray(Create a conventional scaffolding structure)\c
    \bgreen(ar:scaffold)\b \bcyan(model)\b \byellow(field[:type])\b [...] [--force]

HELP;

/* EOF: ./stack/scripts/a_record/locale/en.php */
