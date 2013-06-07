- use \Labourer\Web\Form as form,
      \Labourer\Web\Html as html

section
  = link_to('New <?php echo $name; ?>', url_for('new_<?php echo $name; ?>'))
  = partial('<?php echo $base; ?>/errors.php', compact('error'))
  - $options = array(action => url_for('delete_all_<?php echo $base; ?>'), confirm => '¿Are you sure?')
  = form::delete($options, ~>
    table
      thead
        tr
          th <?php echo strtoupper($pk) . "\n"; ?>
<?php foreach ($fields as $key => $val) { ?>
          th <?php echo $val['title']; ?>

<?php } ?>
      tfoot
        tr
          td { colspan => 99 }
            = form::submit('delete_all', 'Delete selected items')
            = html::ul($<?php echo $base; ?>->navlinks())
      tbody
        - $<?php echo $base; ?>->each(($row) ~>
          tr
            td = form::checkbox("pk[$row-><?php echo $pk; ?>]", $row-><?php echo $pk; ?>)
<?php foreach (array_keys($fields) as $one) { ?>
            td = form::label("pk[$row-><?php echo $pk; ?>]", (string) $row-><?php echo $one; ?> ?: '-')
<?php } ?>
            td = link_to('View', url_for('show_<?php echo $name; ?>', array(':id' => $row-><?php echo $pk; ?>)))
            td = link_to('Edit', url_for('edit_<?php echo $name; ?>', array(':id' => $row-><?php echo $pk; ?>)))
            td
              - $action = url_for('delete_<?php echo $name; ?>', array(':id' => $row-><?php echo $pk; ?>))
              - $params = array(action => $action, method => 'delete', confirm => '¿Are you sure?')
              = link_to('Delete', $params)
