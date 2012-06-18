<?php

/**
 * A record spanish strings
 */

$lang['missing_model_name'] = 'Hace falta el nombre del modelo';
$lang['missing_model_file'] = 'El modelo %{name} no existe';

$lang['model_already_exists'] = 'El modelo %{name} ya existe';
$lang['model_class_building'] = 'Generando clase del modelo %{name}';

$lang['verifying_import'] = 'Comprobando importación';
$lang['verifying_export'] = 'Comprobando exportación';

$lang['import_model_missing'] = 'Hace falta el modelo para importar';
$lang['import_file_missing'] = 'El archivo a importar %{path} no existe';

$lang['export_model_missing'] = 'Hace falta un modelo para exportar';
$lang['export_already_exists'] = 'El archivo a exportar ya existe';
$lang['exporting'] = 'Exportando %{path}';
$lang['importing'] = 'Importando %{path}';

$lang['verifying_structure'] = 'Comprobando estructura';
$lang['unknown_field'] = 'Campo desconocido %{name}';
$lang['unknown_field_type'] = 'Tipo de campo desconocido %{name}:%{type}';
$lang['missing_fields'] = 'Faltan campos';
$lang['crud_already_exists'] = 'Ya existe el CRUD para %{name}';

$lang['a_record_created'] = 'El registro fue creado';
$lang['a_record_updated'] = 'El registro fue actualizado';
$lang['a_record_deleted'] = 'El registro fue eliminado';
$lang['a_record_missing'] = 'No existe el registro';
$lang['a_record_set_deleted'] = 'Los registros seleccionados fueron borrados';
$lang['a_record_set_missing'] = 'Selecciona al menos un registro';
$lang['a_record_required_field'] = 'El campo %{name} es necesario';

$lang['cancel'] = 'Cancelar';
$lang['create_record'] = 'Crear %{name}';
$lang['new_record'] = 'Nuevo %{name}';
$lang['confirm_delete'] = '¿Estás seguro?';
$lang['confirm_delete_all'] = '¿Estás seguro de esto?';
$lang['delete_selected'] = 'Eliminar %{name}s seleccionados';
$lang['update_record'] = 'Actualizar %{name}';
$lang['delete'] = 'Borrar';
$lang['edit'] = 'Editar';

$lang['usage'] = <<<HELP

  \clight_gray(Generador del modelo por defecto)\c
    \bgreen(ar:model)\b \bcyan(name[:table])\b [--parent=class]

  \clight_gray(Copias de seguridad de los modelos de datos)\c
    \bgreen(ar:backup)\b \bcyan(model[:name])\b [--import] [--delete-all]

  \clight_gray(Abre la consola interactiva de a_record)\c
    \bgreen(ar:console)\b

  \clight_gray(Crea una estructura de scaffolding convencional)\c
    \bgreen(ar:scaffold)\b \bcyan(model)\b \byellow(field[:type])\b [...]

HELP;

/* EOF: ./stack/scripts/a_record/locale/es.php */
