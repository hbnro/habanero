<?php

info(ln('db.verifying_schema'));

$out = array();
$schema_file = CWD.DS.'db'.DS.'schema'.EXT;

$path = str_replace(CWD.DS, '', $schema_file);
success(ln('db.updating_schema', array('path' => $path)));

foreach (db::tables() as $one) {
  $pad = str_repeat(' ', strlen($one) + 17);

  $out []= sprintf("create_table('$one', array(");

  foreach (db::columns($one) as $key => $val) {
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

write($schema_file, sprintf("<?php\n/* %s */\n%s\n", date('Y-m-d H:i:s'), join("\n", $out)));

/* EOF: ./stack/library/db/scripts/schema.php */
