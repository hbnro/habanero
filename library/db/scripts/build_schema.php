<?php

info(ln('db.verifying_schema'));

$out = array();
$schema_file = mkpath(getcwd().DS.'database').DS.'schema'.EXT;

$path = str_replace(getcwd().DS, '', $schema_file);
success(ln('db.updating_schema', array('path' => $path)));

foreach (db::tables() as $one) {
  $pad = str_repeat(' ', strlen($one) + 17);

  $out []= sprintf("create_table('$one', array(");

  foreach (db::columns($one) as $key => $val) {
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

write($schema_file, sprintf("<?php\n/* %s */\n%s\n", date('Y-m-d H:i:s'), join("\n", $out)));

/* EOF: ./library/db/scripts/schema.php */
