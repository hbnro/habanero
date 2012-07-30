<?php

chdir(__DIR__);

require 'habanero/framework/initialize.php';

import(array('application', 'helpers', 'tamal'));
import(array('development' => array('chess', 'coffee')));

run(function () {
});
