<?php

$name = cli::flag('name', $callback);
$time = time();

$migration_name = date('YmdHis_', $time).$args[0].'_'.$name;
$migration_path = mkpath(APP_PATH.DS.'database'.DS.'migrate');
$migration_file = $migration_path.DS.$migration_name.EXT;


foreach ($args as $i => $one) {
  if (is_array($one)) {
    $text = var_export($one, TRUE);

    $text = preg_replace('/ \d+\s+=>/', '', $text);
    $text = preg_replace('/array\s+\(/', 'array(', $text);
    $text = preg_replace('/[\'"](\d+)[\'"]/', '\\1', $text);
    $text = preg_replace('/([\'"]\w+[\'"])\s+=>\s+(?=\w+)/s', '\\1 => ', $text);

    $text = str_replace('( ', '(', $text);
    $text = str_replace(',)', ')', $text);

    $args[$i] = $text;
  } else {
    $args[$i] = "'$one'";
  }
}


$code = sprintf("$callback(%s);\n", join(', ', $args));

if ( ! is_file($migration_file)) {
  $date = date('Y-m-d H:i:s', $time);

  write($migration_file, "<?php\n/* $date */\n$code");
} else {
  write($migration_file, $code, 1);
}

eval($code);

/* EOF: ./library/db/scripts/build_migration.php */
