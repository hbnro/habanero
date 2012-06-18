- javascript_for('app')
- stylesheet_for('app')

section
  = link_to('<?php echo ln('ar.new_record', array('name' => $model)); ?>', url_for::new_<?php echo $model; ?>())
  - $options = array(action => url_for::delete_all_<?php echo $model; ?>s(), confirm => '<?php echo ln('ar.confirm_delete_all'); ?>')
  = form::delete($options, ~>
    table
      thead
        tr
          th <?php echo strtoupper($pk) . "\n"; ?>
<?php foreach ($fields as $key => $val) { ?>
          th <?php echo $val['ln']; ?>

<?php } ?>
      tfoot
        tr
          td { colspan => 99 }
            = form::submit('delete_all', '<?php echo ln('ar.delete_selected', array('name' => $model)); ?>')
      tbody
        - $<?php echo $model; ?>s->each(($row) ~>
          tr
            td = form::checkbox("pk[$row-><?php echo $pk; ?>]", $row-><?php echo $pk; ?>)
<?php foreach (array_keys($fields) as $one) { ?>
            td = form::label("pk[$row-><?php echo $pk; ?>]", $row-><?php echo $one; ?>)
<?php } ?>
            td = link_to('<?php echo ln('ar.edit'); ?>', url_for::edit_<?php echo $model; ?>(array(id => $row-><?php echo $pk; ?>)))
            td
              - $action = url_for::delete_<?php echo $model; ?>(array(id => $row-><?php echo $pk; ?>))
              - $params = array(action => $action, method => 'delete', confirm => '<?php echo ln('ar.confirm_delete'); ?>')
              = link_to('<?php echo ln('ar.delete'); ?>', $params)
