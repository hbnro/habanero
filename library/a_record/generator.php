<?php

require __DIR__.DS.'initialize'.EXT;

app_generator::usage(ln('ar.generator_title'), ln('ar.generator_usage'));

app_generator::alias('console', 'c');


// inspect records
app_generator::implement('console', function () {
  import('a_record');

  $scope = new stdClass;

  cli::main(function ()
    use($scope) {

    $test = cli::readln('>>> ');

    if (in_array($test, array('exit', 'quit'))) {
      cli::quit();
    } else {
      // TODO: implement more expressions!
      if (preg_match('/^(\w+\.\w+)(?:\s+(.*?))?$/', $test, $match)) {
        $test = explode('.', $match[1]);
        $args = explode(' ', ! empty($match[2]) ? trim($match[2]) : '');

        $out  = @$test[0]::apply($test[1], $args);

        pretty(function ()
          use($out) {
          if (is_array($out)) {
            foreach ($out as $one) {
              printf(">>> $one\n");
            }
          } else {
            printf(">>> $out\n");
          }
        });
      }
    }
  });
});

/* EOF: ./library/a_record/generator.php */
