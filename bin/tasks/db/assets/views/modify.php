- use Labourer\Web\Form as Form

section
  = link_to('Cancel', url_for('show_<?php echo $name; ?>', array(':id' => $<?php echo $name; ?>-><?php echo $pk; ?>)))
  = partial('<?php echo $base; ?>/errors.php', compact('error'))
  = Form::put(url_for('update_<?php echo $name; ?>', array(':id' => $<?php echo $name; ?>-><?php echo $pk; ?>)), ~>
<?php foreach ($fields as $key => $val) { ?>
    div = Form::field(array(type => '<?php echo $val['type']; ?>', name => 'row[<?php echo $key; ?>]', label => '<?php echo $val['title']; ?>', value => $<?php echo $name; ?>-><?php echo $key; ?>))
<?php } ?>
    = Form::submit('update', 'Update <?php echo $name; ?>')
    - $action = url_for('delete_<?php echo $name; ?>', array(':id' => $<?php echo $name; ?>-><?php echo $pk; ?>))
    - $params = array(action => $action, method => 'delete', confirm => '¿Are you sure?')
    = link_to('Delete', $params)
