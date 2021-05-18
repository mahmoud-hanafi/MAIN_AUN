<?php

/**
 * @file
 * Prepare content info note.
 */
if (count($multival_field) || count($fields_required)) {
?>
<div id='replace_breif_note_div' class='content-type-details field-info'>
  <p class='important-info'>Important!</p>
  <?php if (count($fields_required)){ ?>
    <p>Mandotory Fields are:</p>
    <ul>
    <?php foreach ($fields_required as $field) { ?>
       <li><?php print $field; ?></li>
    <?php } ?>
     </ul>
  <?php } ?>
  <?php if (count($multival_field)){ ?>
    <p><?php print t('Multivalued Fields are:'); ?></p>
    <ul>
    <?php foreach ($multival_field as $field) { ?>
       <li><?php print $field; ?></li>
    <?php } ?>
    </ul>
    <p><?php print t('Note: In Multivalue Field List "()" braces contains maximum allowed values.'); ?></p>
  <?php } ?>
</div>
<?php } else { ?>
<div id='replace_breif_note_div' class='empty-content-type-details field-info'>
</div>
<?php } ?>
