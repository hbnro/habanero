<?php

/**
 * Web development framework for php5.3+
 *
 * @author Alvaro Cabrera (@pateketrueke)
 * @link   https://github.com/pateketrueke/habanero
 */

// bundled full-stack
$autoload = require 'vendor/autoload.php';

Sauce\Base::$autoload = $autoload;

return $autoload;
