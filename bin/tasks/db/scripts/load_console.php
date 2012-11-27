<?php

$warn = arg('s run');
$prefix = $warn ? "\cwhite,red(*BIO HAZZARD*)\c\n  " : '';

say("\n  {$prefix}Press \bwhite(CTRL+C)\b to exit\n");

$cache = array();

if ($readline = function_exists('readline')) {
  readline_completion_function(function () {
    return array();
  });
}

$callback = $readline ? 'readline' : '\\Sauce\\Shell\\CLI::read';

\Sauce\Shell\CLI::main(function ()
  use($callback, $readline, &$cache, $warn) {
    $_ = trim(call_user_func($callback, colorize('  > ')));

    if ( ! $_) {
      return;
    } elseif ($readline && $_ && ! in_array($_, $cache)) {
      readline_add_history($_);
      $cache []= $_;
    }


    $code = "extract(__set());return __set($_,get_defined_vars());";
    $out = $warn ? (array) @eval($code) : @assert($_);

    if (is_array($out)) {
      foreach ($out as $key => $one) {
        $prefix = '';

        if (is_string($key)) {
          $prefix = "\clight_gray($key )\c";
        }

        $one = preg_replace('/^/m', colorize("  \cgreen(>)\c $prefix"), trim(inspect($one)));
        writeln($one);
      }
    } else {
      writeln(colorize(sprintf('  \c%s(> %s)\c', $out ? 'green' : 'red', $_)));
    }

  });
