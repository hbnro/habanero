<?php

info(ln('db.verifying_schema'));

$out = array();

$clean = function ($text) {
  $text = preg_replace('/[\r\n]\s*/', '', $text);
  $text = preg_replace('/\d+\s*=>\s*/', '', $text);
  $text = strtr($text, array(
    'array ' => 'array',
    'false' => 'FALSE',
    'true' => 'TRUE',
    ',)' => ')',
    ',' => ', ',
  ));
  return $text;
};

$schema_file = mkpath(APP_PATH.DS.'database').DS.'schema'.EXT;

$path = str_replace(APP_PATH.DS, '', $schema_file);
success(ln('db.updating_schema', array('path' => $path)));

foreach (db::tables() as $one) {
  $out []= "  '$one' => array(";
  $out []= "    'columns' => array(";

  foreach (db::columns($one) as $key => $val) {
    $defs  = $clean(var_export($val, TRUE));
    $out []= "      '$key' => $defs,";
  }

  $out []= '    ),';
  $out []= "    'index' => array(";

  foreach (db::indexes($one) as $key => $val) {
    $idx   = $clean(var_export($val, TRUE));
    $out []= "      '$key' => $idx,";
  }

  $out []= '    ),';
  $out []= '  ),';
}

write($schema_file, sprintf("<?php return array(\n%s\n);\n/* %s */\n", join("\n", $out), date('Y-m-d H:i:s')));

/* EOF: ./stack/scripts/db/scripts/schema.php */
