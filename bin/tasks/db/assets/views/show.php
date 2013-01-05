section
  = link_to('Show all', url_for('<?php echo $base; ?>'))
  dl
<?php foreach ($fields as $key => $val) { ?>
    dt <?php echo $val['title']; ?>

    dd
      = $<?php echo $name; ?>-><?php echo $key; ?>

<?php } ?>
  = link_to('Edit', url_for('edit_<?php echo $name; ?>', array(':id' => $<?php echo $name; ?>-><?php echo $pk; ?>)))
  - $action = url_for('delete_<?php echo $name; ?>', array(':id' => $<?php echo $name; ?>-><?php echo $pk; ?>))
  - $params = array(action => $action, method => 'delete', confirm => '¿Are you sure?')
  = link_to('Delete', $params)
