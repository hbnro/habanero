<?php


@list($name) = array_shift($params);

if ( ! $name) {
  error("\n  Missing model name\n");
} else {
  info("\n  Model schema:\n");
}

#if ( ! $name) {
  #error(ln('ar.missing_model_name'));
#} else {
  #$out_file = mkpath(APP_PATH.DS.'models').DS.$name.EXT;

 # if (is_file($out_file)) {
 #   error(ln('ar.model_already_exists', array('name' => $name)));
  #} else {
 #   success(ln('ar.model_class_building', array('name' => $name)));
 #   add_class($out_file, $name, cli::flag('parent') ?: 'db_model', '', array(), $table ? compact('table') : array());
  #}
#}
