<?php

if (cli::flag('import')) {
  info(ln('db.verifying_import'));

  if ( ! $name) {
    error(ln('db.import_name_missing'));
  } else {
    $inc_file  = CWD.DS.'db'.DS.'backup'.DS.$name;
    $inc_file .= cli::flag('raw') ? '.sql' : EXT;

    $path = str_replace(CWD.DS, '', $inc_file);

    if ( ! is_file($inc_file)) {
      error(ln('db.import_file_missing', array('path' => $path)));
    } else {
      success(ln('db.importing', array('path' => $path)));

      db::import($inc_file, cli::flag('raw'));
    }
  }
} else {
  info(ln('db.verifying_export'));

  if ( ! $name) {
    error(ln('db.export_name_missing'));
  } else {
    $name = preg_replace('/\W/', '_', $name);

    $data = cli::flag('data');
    $raw  = cli::flag('raw');
    $ext  = $raw ? '.sql' : EXT;

    $out_file = mkpath(CWD.DS.'db'.DS.'backup').DS.$name.$ext;

    if (is_file($out_file)) {
      error(ln('db.export_already_exists'));
    } else {
      $path = str_replace(CWD.DS, '', $out_file);

      touch($out_file);
      success(ln('db.exporting', array('path' => $path)));
      db::export($out_file, '*', $data, $raw);
    }
  }
}

bold(ln('tetl.done'));

/* EOF: ./stack/library/db/scripts/backup.php */
