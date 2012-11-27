<?php

$path = array_shift($params);

if ( ! $path) {
  error("\n  Missing model path\n");
} else {
  $mod_path = path(APP_PATH, $path);

  if ( ! is_dir($mod_path)) {
    error("\n  Model path '$path' does not exists\n");
  } else {
    $crawl = function ($file) {
        if (is_file($file) && (\IO\File::ext($file) === 'php')) {
          preg_match_all('/class\s(\S+)\s/', read($file), $match);

          require $file;
          foreach ($match[1] as $klass) {
            $re = new \ReflectionClass($klass);

            switch ($re->getParentClass()->getName()) {
              case 'Servant\\Mapper\\Database';
                status('hydrate', $file);

                $dsn = option('database.' . $klass::CONNECTION);
                $db = \Grocery\Base::connect($dsn);

                $columns = $klass::columns();
                $indexes = $klass::indexes();

                if ( ! isset($db[$klass::table()])) {
                  $db[$klass::table()] = $columns;
                }

                \Grocery\Helpers::hydrate($db[$klass::table()], $columns, $indexes);
              break;
              case 'Servant\\Mapper\\MongoDB';
                status('hydrate', $file);

                $dsn_string = \Servant\Config::get($klass::CONNECTION);
                $database = substr($dsn_string, strrpos($dsn_string, '/') + 1);
                $mongo = $dsn_string ? new \Mongo($dsn_string) : new \Mongo;
                $db = $mongo->{$database ?: 'default'};

                \Servant\Helpers::reindex($db->{$klass::table()}, $klass::indexes());
              break;
              default;
              break;
            }
          }
        }
      };

    if (arg('R recursive')) {
      \IO\Dir::each($mod_path, '*.php', $crawl);
    } else {
      \IO\Dir::open($mod_path, $crawl);
    }
  }
}
