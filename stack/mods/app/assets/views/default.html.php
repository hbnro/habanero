<!DOCTYPE html>

<!--[if lt IE 7 ]> <html class="ie ie6 no-js" lang="en"> <![endif]-->
<!--[if IE 7 ]>    <html class="ie ie7 no-js" lang="en"> <![endif]-->
<!--[if IE 8 ]>    <html class="ie ie8 no-js" lang="en"> <![endif]-->
<!--[if IE 9 ]>    <html class="ie ie9 no-js" lang="en"> <![endif]-->
<!--[if gt IE 9]><!--><html class="no-js" lang="en"><!--<![endif]-->

  <head>
    <meta charset="<?php echo CHARSET; ?>">

    <title><?php echo $title; ?></title>

<?php echo assets::tag_for('modernizr-1.7.min.js'); ?>
<?php echo assets::favicon(); ?>
<?php echo assets::before(); ?>
<?php echo $head; ?>

  </head>
  <body>
  <div id="wrapper">
<?php echo $body; ?>
    <footer>
      &mdash; <?php echo ticks(BEGIN); ?>s
    </footer>
  </div>

    <script src="//ajax.googleapis.com/ajax/libs/jquery/1.5.1/jquery.min.js"></script>
    <script>window.jQuery || document.write("<script src='<?php echo assets::url_for('jquery-1.5.1.min.js'); ?>'>\x3C/script>")</script>
<?php echo assets::after(); ?>

<!--
    <script>

      var _gaq = _gaq || [];
      _gaq.push(['_setAccount', 'UA-XXXXXX-XX']);
      _gaq.push(['_trackPageview']);

      (function () {
        var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
        ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
        var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
      })();

    </script>
-->

  </body>
</html>
