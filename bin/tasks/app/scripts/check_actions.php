<?php

@list($name, $action) = explode(':', array_shift($params));

if ( ! $name) {
  error("\n  Missing controller name\n");
} elseif ($action) {
  require path(__DIR__, 'create_action.php');
} else {
  require path(__DIR__, 'create_controller.php');
}
