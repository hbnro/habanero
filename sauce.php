<?php

/**
 * Web development framework for php5.3+
 *
 * @author Alvaro Cabrera (@pateketrueke)
 * @link   https://github.com/pateketrueke/habanero
 */

// bundled full-stack
$autoload = require __DIR__.DIRECTORY_SEPARATOR.'vendor'.DIRECTORY_SEPARATOR.'autoload.php';

Sauce\Base::$autoload = $autoload;

return $autoload;
