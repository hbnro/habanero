- javascript_for('app')
- stylesheet_for('app')

section
  = link_to('Show all', url_for('<?php echo $base; ?>'))
  dl
<?php foreach ($fields as $key => $val) { ?>
    dt <?php echo $val['title']; ?>

    dd
      ~ $<?php echo $name; ?>-><?php echo $key; ?>

<?php } ?>
