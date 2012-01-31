<?php

/**
 * YAML library support
 */

/**#@+
 * @ignore
 */
require __DIR__.DS.'vendor'.DS.'spyc'.EXT;
require __DIR__.DS.'functions'.EXT;

if ( ! function_exists('yaml_parse')) {
  function yaml_parse($text) {
    return spyc_load($text);
  }

  function yaml_parse_url($link) {
    return spyc_load(read($link));
  }

  function yaml_parse_file($text) {
    return spyc_load_file($link);
  }

  function yaml_emit($data) {
    return Spyc::YAMLDump($data);
  }

  function yaml_emit_file($file, $data) {
    return write($file, Spyc::YAMLDump($data));
  }
}
/**#@-*/

/* EOF: ./library/yaml/initialize.php */
