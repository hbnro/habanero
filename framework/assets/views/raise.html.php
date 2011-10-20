<!doctype html>
<html>
  <head>
    <meta charset="UTF-8">
    <title><?php echo ln('error'); ?></title>
    <style>
    p {
      padding: .5em;
      font-size: .9em;
      background: #ededed;
      font-family: Palatino, "Palatino Linotype", "Hoefler Text", Times, "Times New Roman", serif;
    }
    pre {
      overflow: auto;
      font-family: Monaco, "Bitstream Vera Sans Mono", "Lucida Console", Terminal, monospace;
      font-size: .8em;
    }
    h3 {
      border-bottom: 3px dotted #dedede;
      font-family: "Lucida Sans", "Lucida Grande", Lucida, sans-serif;
      font-size: 1.3em;
    }
    </style>
  </head>
  <body>
    <p><?php echo ents($message, TRUE); ?></p>
    <h3><?php echo ln('application'); ?></h3>
    <?php dump(array(
      'user' => "$user@$host",
      'route' => $route,
      'method' => value($_SERVER, 'REQUEST_METHOD'),
      'params' => function_exists('params') ? params() : array(),
      'bootstrap' => APP_LOADER,
    ), TRUE); ?>
    <?php if (isset($vars)) { ?><h3><?php echo ln('request_vars'); ?></h3>
    <?php dump($vars, TRUE); } ?>
    <?php if (isset($headers)) { ?><h3><?php echo ln('response_headers'); ?></h3>
    <?php dump($headers, TRUE); } ?>
    <?php if (isset($received)) { ?><h3><?php echo ln('request_headers'); ?></h3>
    <?php dump($received, TRUE); } ?>
    <?php if (isset($backtrace)) { ?><h3><?php echo ln('backtrace'); ?></h3>
    <?php dump($backtrace, TRUE); } ?><h3><?php echo ln('includes'); ?></h3>
    <?php dump(get_included_files(), TRUE); ?>
    <h3><?php echo ln('configuration'); ?></h3>
    <?php dump(config(), TRUE); ?>
    <?php if (isset($env)) { ?><h3><?php echo ln('environment'); ?></h3>
    <?php dump($env, TRUE); } ?>
    <p>&mdash; <?php echo ticks(defined('BEGIN') ? BEGIN : 0); ?>s</p>
  </body>
</html>
<?php exit;
