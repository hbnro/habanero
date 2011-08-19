<!doctype html>
<html>
  <head>
    <meta charset="<?= CHARSET; ?>">
    <title><?= ln('error'); ?></title>
    <style><!-- TODO: stylish --></style>
  </head>
  <body>
    <pre><?= ents($message, TRUE); ?></pre>
    <h3><?= ln('application'); ?></h3>
    <?php dump(array(
      'user' => "$user@$host",
      'route' => $route,
      'params' => function_exists('params') ? params() : array(),
      'bootstrap' => APP_LOADER,
    ), TRUE); ?>
    <h3><?= ln('configuration'); ?></h3>
    <?php dump(config(), TRUE); ?>
    <?php if (isset($env)) { ?><h3><?= ln('environment'); ?></h3>
    <?php dump($env, TRUE); } ?>
    <?php if (isset($headers)) { ?><h3><?= ln('headers'); ?></h3>
    <?php dump($headers, TRUE); } ?>
    <?php if (isset($backtrace)) { ?><h3><?= ln('backtrace'); ?></h3>
    <?php dump($backtrace, TRUE); } ?><h3><?= ln('includes'); ?></h3>
    <?php dump(get_included_files(), TRUE); ?>
    <p><?php echo ticks(defined('BEGIN') ? BEGIN : 0); ?>s</p>
  </body>
</html>
<?php exit;