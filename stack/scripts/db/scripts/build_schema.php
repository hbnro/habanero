<?php

info(ln('db.verifying_schema'));

$out =
$set = array();

$test = db::tables();

if (($key = array_search('migration_history', $test)) !== FALSE) {
  unset($test[$key]);
}

$tables_file = mkpath(APP_PATH.DS.'config').DS.'tables'.EXT;
$schema_file = mkpath(APP_PATH.DS.'database').DS.'schema'.EXT;

$path = str_replace(APP_PATH.DS, '', $schema_file);
success(ln('db.updating_schema', array('path' => $path)));

foreach ($test as $one) {
  $set[$one] = db::columns($one);
  $pad = str_repeat(' ', strlen($one) + 17);

  $out []= sprintf("create_table('$one', array(");

  foreach ($set[$one] as $key => $val) {
    $val['type'] = str_replace('datetime', 'timestamp', $val['type']);

    $def = array("'{$val['type']}'");

    $val['length'] && $def []= $val['length'];

    $out []= sprintf("$pad  '$key' => array(%s),", join(', ', $def));
  }

  $out []= "$pad), array('force' => TRUE));";
  $out []= '';

  foreach (db::indexes($one) as $key => $val) {
    $def  = array("'name' => '$key'");
    $cols = "'" . join("', '", $val['column']) . "'";

    ! empty($val['unique']) && $def []= "'unique' => TRUE";

    $out []= sprintf("add_index('$one', array($cols), array(%s));", join(', ', $def));
  }
  $out []= '';
}

write($tables_file, sprintf("<?php return %s;\n", var_export($set, TRUE)));
write($schema_file, sprintf("<?php\n/* %s */\n%s\n", date('Y-m-d H:i:s'), join("\n", $out)));

/* EOF: ./stack/scripts/db/scripts/schema.php */
