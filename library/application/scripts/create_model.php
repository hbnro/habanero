<?php

@list($name, $table) = explode(':', $name);

$out_file = mkpath(CWD.DS.'app'.DS.'models').DS.$name.EXT;

if (is_file($out_file)) {
  error(ln('app.model_already_exists', array('name' => $name)));
} else {
  success(ln('app.model_class_building', array('name' => $name)));

  $type   = cli::flag('parent') ?: 'db_model';
  $parent = $table ? "\n  public static \$table = '$table';" : '';
  $code   = "<?php\n\nclass $name extends $type"
          . "\n{{$parent}\n}\n";

  write($out_file, $code);
}

/* EOF: ./library/application/scripts/create_model.php */
