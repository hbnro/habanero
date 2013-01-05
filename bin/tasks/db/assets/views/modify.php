- use Labourer\Web\Form as form

section
  = link_to('Cancel', url_for('show_<?php echo $name; ?>', array(':id' => $<?php echo $name; ?>-><?php echo $pk; ?>)))
  = partial('<?php echo $base; ?>/errors.php', compact('error'))
  = form::put(url_for('update_<?php echo $name; ?>', array(':id' => $<?php echo $name; ?>-><?php echo $pk; ?>)), ($f) ~>
<?php foreach ($fields as $key => $val) { if (in_array($val['type'], array('hidden', 'text', 'textarea', 'number', 'file', 'datetime', 'date', 'time'))) { ?>
    div = form::field(array(type => '<?php echo $val['type']; ?>', name => 'row[<?php echo $key; ?>]', label => '<?php echo $val['title']; ?>', value => $<?php echo $name; ?>-><?php echo $key; ?>))
<?php } elseif ($val['type'] === 'checkbox') { ?>
    div = form::field(array(type => 'checkbox', name => 'row[<?php echo $key; ?>]', label => '<?php echo $val['title']; ?>', value => 'on', options => array(default => $<?php echo $name; ?>-><?php echo $key; ?>)))
<?php } else { ?>
    div = form::field(array(type => '<?php echo $val['type'] === 'json' ? 'textarea' : 'text' ; ?>', name => 'row[<?php echo $key; ?>]', label => '<?php echo $val['title']; ?>', value => $<?php echo $name; ?>-><?php echo $key; ?>))
<?php } } ?>
    = form::submit('update', 'Update <?php echo $name; ?>')
    - $action = url_for('delete_<?php echo $name; ?>', array(':id' => $<?php echo $name; ?>-><?php echo $pk; ?>))
    - $params = array(action => $action, method => 'delete', confirm => '¿Are you sure?')
    = link_to('Delete', $params)
