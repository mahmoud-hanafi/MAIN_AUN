<?php

/**
 * @file
 * Prepare mapping help text.
 */
?>
<div class="form-item form-type-item" id="edit-helptext">
Note:
<ul>
  <li>Content type Field(s) are the fields of selected content type present in existing site.</li>
  <li>CSV Column(s) are the column name provided in the uploaded CSV file.</li>
  <li>User can select from right side 'CSV Column(s)' to assign it's value to the corresponding Content Field.</li>
  <li>To avoid import failure, map your CSV columns to the appropriate Content type fields.</li>
  <li>For boolean type fields, use 'y' or '1' for TRUE and 'n' or '0' for FALSE in source file.</li>
  <li>For multivalued field values, provide each value in separate column in CSV file. Select all the columns for respective field as shown below: <br /><a href=<?php print $filepath; ?> target='_blank'><img src=<?php print $filepath; ?> alt='mappingUI' width='25%' ></a></li>
<?php if ($allowed_date_format != NULL) { ?>
  <li>For this content type, allowed Date format should be <?php print $allowed_date_format; ?></li>
</ul>
<?php } else { ?>
</ul>
<?php } ?>
</div>
