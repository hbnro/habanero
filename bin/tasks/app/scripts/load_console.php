<?php

say("\n  Press \bwhite(CTRL+C)\b to exit\n");

$cache = array();

if ($readline = function_exists('readline')) {
  readline_completion_function(function () {
    return array();
  });
}

$callback = $readline ? 'readline' : '\\Sauce\\Shell\\CLI::read';

\Sauce\Shell\CLI::main(function ()
  use($callback, $readline, &$cache) {
    $_ = trim(call_user_func($callback, colorize('  > ')));

    if ($readline && $_ && ! in_array($_, $cache)) {
      readline_add_history($_);
      $cache []= $_;
    }

    $code = "extract(__set());return __set($_,get_defined_vars());";
    $out = (array) @eval($code);

    foreach ($out as $key => $one) {
      $prefix = '';

      if (is_string($key)) {
        $prefix = "\clight_gray($key: )\c";
      }

      $one = print_r($one, TRUE);
      $one = preg_replace('/^/m', colorize("  \bgreen(>)\b $prefix"), $one);

      \Sauce\Shell\CLI::write("$one\n");
    }

  });
