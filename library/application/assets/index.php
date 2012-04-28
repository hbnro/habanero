<?php

chdir(__DIR__);

require 'tetlphp/framework/initialize.php';

import(array('application', 'helpers', 'tamal'));
import(array('development' => array('chess', 'coffee')));

run(function () {
});
