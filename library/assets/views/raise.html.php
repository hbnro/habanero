<!DOCTYPE html>
<html>
  <head>
    <title>Error</title>
    <meta charset="UTF-8">
  </head>
  <body>
    <pre><?php echo ents($message, TRUE); ?></pre>
    <h3>Application</h3>
    <?php dump(array(
      'user' => "$user@$host",
      'route' => $route,
      'params' => params(),
      'bootstrap' => APP_LOADER,
    ), TRUE); ?>
    <h3>Config</h3>
    <?php dump(config(), TRUE); ?>
    <?php if (isset($env)) { ?><h3>ENV</h3>
    <?php dump($env, TRUE); } ?>
    <?php if (isset($global)) { ?><h3>GLOBAL</h3>
    <?php dump($global, TRUE); } ?>
    <?php if (isset($headers)) { ?><h3>Headers</h3>
    <?php dump($headers, TRUE); } ?>
    <?php if (isset($backtrace)) { ?><h3>Backtrace</h3>
    <?php dump($backtrace, TRUE); } ?><h3>Includes</h3>
    <?php dump(get_included_files(), TRUE); ?>
    <p><?php echo ticks(defined('BEGIN') ? BEGIN : 0); ?>s</p>
  </body>
</html>
<?php exit;