<?php

/**
 * Yaml helpers
 */

/**
 * Lambda parsing as block
 *
 * @param  mixed Function callback
 * @return mixed
 */
function yaml_block(Closure $lambda) {
  ob_start() && $lambda();

  $test = ob_get_clean();

  preg_match('/^(\s*?)---/', $test, $match);

  ! empty($match[1]) && $indent = strlen($match[1]);

  $indent && $test = preg_replace("/^\s{{$indent}}/m", '', $test);

  return yaml_parse($test);
}

/* EOF: ./library/yaml/functions.php */
