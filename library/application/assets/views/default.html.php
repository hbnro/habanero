<!DOCTYPE html>

<!--[if lt IE 7 ]> <html class="ie ie6 no-js" lang="en"> <![endif]-->
<!--[if IE 7 ]>    <html class="ie ie7 no-js" lang="en"> <![endif]-->
<!--[if IE 8 ]>    <html class="ie ie8 no-js" lang="en"> <![endif]-->
<!--[if IE 9 ]>    <html class="ie ie9 no-js" lang="en"> <![endif]-->
<!--[if gt IE 9]><!--><html class="no-js" lang="en"><!--<![endif]-->

  <head>
    <meta charset="UTF-8">

    <title><?php echo $title; ?></title>

<?php echo assets::tag_for('http://fonts.googleapis.com/css?family=Bangers', 'css'); ?>
<?php echo assets::tag_for('modernizr-2.0.6.min.js'); ?>
<?php echo assets::favicon(); ?>
<?php echo assets::before(); ?>
<?php echo $head; ?>

  </head>
  <body>
  <div id="wrapper">
    <header>
      <?php echo $title; ?>!
  </header>
<?php echo $body; ?>
    <footer>
      &mdash; <?php echo ticks(BEGIN); ?>s
    </footer>
  </div>

    <script src="//ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js"></script>
    <script>window.jQuery || document.write("<script src='<?php echo assets::url_for('jquery-1.7.1.min.js'); ?>'>\x3C/script>")</script>
<?php echo assets::after(); ?>

  <!--
    <script>
      window._gaq = [['_setAccount','UAXXXXXXXX1'],['_trackPageview'],['_trackPageLoadTime']];
      Modernizr.load({
        load: ('https:' == location.protocol ? '//ssl' : '//www') + '.google-analytics.com/ga.js'
      });
    </script>
  -->

  <!--[if lt IE 7 ]>
    <script defer src="//ajax.googleapis.com/ajax/libs/chrome-frame/1.0.3/CFInstall.min.js"></script>
    <script defer>window.attachEvent('onload',function(){CFInstall.check({mode:'overlay'})})</script>
  <![endif]-->

  </body>
</html>
