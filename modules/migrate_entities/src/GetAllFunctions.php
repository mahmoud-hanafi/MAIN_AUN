<?php

namespace Drupal\migrate_entities;

use Drupal\migrate_plus\Entity\MigrationGroup;
use Drupal\migrate_plus\Entity\Migration;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Class GetAllFunctions.
 */
class GetAllFunctions {

  /**
   * Call migration submit function to register migration.
   */
  public static function migrationSubmit($form_state) {
    // Define blank arrays and variables.
    $fileValue = [];
    $imageValue = [];
    $type_result = [];
    $termValue = [];
    $constants = [];
    $file_uri = '';
    $form_state->cleanValues();

    $sessionVariable = \Drupal::service('user.private_tempstore')->get('simple_node_importer');
    $parameters = $sessionVariable->get('parameters');
    $snp_nid = $parameters['node'];
    $node_storage = \Drupal::entityTypeManager()->getStorage('node')->load($snp_nid);
    $node = $node_storage->load($snp_nid);
    $fid = $node->get('field_upload_csv')->getValue()[0]['target_id'];
    $file_storage = \Drupal::entityTypeManager()->getStorage('file')->load($fid);
    $bundleType = $node_storage->get('field_select_content_type')->getValue()[0]['value'];
    $entityType = $node_storage->get('field_select_entity_type')->getValue()[0]['value'];
    $map_values = $sessionVariable->get('mapvalues');
    $file = $file_storage->load($fid);
    $csv_uri = $file->getFileUri();
    $handle = fopen($csv_uri, 'r');
    $columns = [];
    $service = \Drupal::service('snp.get_services');
    $columns = array_values($service->simpleNodeImporterGetAllColumnHeaders($csv_uri));
    $map_fields = array_keys($map_values);
    $i = 1;
    $id = 1;
    while ($row = fgetcsv($handle)) {
      if ($i == 1) {
        $i++;
        continue;
      }
      $record = [];
      $record['id'] = $id;
      foreach ($row as $k => $field) {
        $column1 = str_replace(' ', '_', strtolower($columns[$k]));
        foreach ($map_fields as $field_name) {
          if ($map_values[$field_name] == $column1) {
            $record[$field_name] = trim($field);
          }
          else {
            if (is_array($map_values[$field_name])) {
              $multiple_fields = array_keys($map_values[$field_name]);
              foreach ($multiple_fields as $j => $m_fields) {
                if ($m_fields == $column1) {
                  if (!empty($field)) {
                    $record[$field_name][$j] = trim($field);
                  }
                  else {
                    $record[$field_name][$j] = NULL;
                  }
                }
              }
            }
          }
        }
      }

      // Check if status if given then set 0 or 1.
      if (!empty(($record['status']))) {
        if ($record['status'] == 0 && strtolower($record['status']) == 'false') {
          $record['status'] = 0;
        }
        else {
          $record['status'] = 1;
        }
      }
      else {
        $record['status'] = 1;
      }
      // Set user uid according to 0 or 1.
      $record['uid'] = !empty($record['uid']) ? $record['uid'] : '1';
      $id++;

      /* If field is multivalues then
       * concatenate with ;
       * and place in a single column.
       */
      foreach ($record as $rec => $value) {
        if (is_array($value) && !empty($value)) {
          $multi_array = [];
          foreach ($value as $var_key => $var_item) {
            if (!empty($var_item)) {
              $multi_array[] = $var_item;
            }
          }
          $record[$rec] = implode(";", $multi_array);
        }
      }
      $records[] = $record;
    }

    $assocDataArray = $records;
    $fileName = $bundleType . '_template.csv';

    /* Generate new CSV file and
     * placed on a specific folder and
     * get the file object.
     */
    $new_file = self::coutputCsv($fileName, $assocDataArray);
    $file_uri = $new_file->values['uri']['x-default'][0]['value'];
    $file_uri = $new_file->get('uri')->getValue();
    $file_path = $file_uri[0]['value'];
    if (!empty($file_uri)) {
      /* Get the uploaded file path */
      $file_uri = explode('public://upload_csv/', $file_path);

      /* Get the file name */
      $file_name = $file_uri[1];
      /* Convert the CSV into array */
      $data = self::csvToArray($file_path, ',');

      /* Mapping fields */
      $header_row = self::csvHeader($file_path);
      array_shift($header_row);

      foreach ($header_row as $key => $value) {
        $process[$value] = $value;

        // Set expode plugin to migrate other mutivalues fields.
        $process[$value] = [
          'plugin' => 'explode',
          'source' => $value,
          'delimiter' => ';',
        ];

        // Fetch each field definition.
        $type_result = self::fieldTermType($value, $bundleType);

        if ($type_result['entity_type'] == 'file' && $type_result['field_type'] == 'file') {
          $fileValue[] = $value;
        }

        if ($type_result['entity_type'] == 'file' && $type_result['field_type'] == 'image') {
          $imageValue[] = $value;
        }

        if ($type_result['entity_type'] == 'taxonomy_term' && $type_result['field_type'] == 'entity_reference') {
          $termValue[] = $value;
        }
      }
      $process += [
        'type'  => [
          'plugin'        => 'default_value',
          'default_value' => $bundleType,
        ],
      ];

      // Check condition for file.
      if (!empty($fileValue)) {
        foreach ($fileValue as $key_array => $key_value) {
          $process[$key_value] = self::fileProcess($key_value, $bundleType);
        }
        $files = [
          'file_destination' => 'public://new_file/',
        ];
      }
      $file = !empty($files) ? $files : [];
      // Check condition for image.
      if (!empty($imageValue)) {
        foreach ($imageValue as $key_array => $key_value) {
          $process[$key_value] = self::imageProcess($key_value, $bundleType);
        }
        $images = [
          'image_destination' => 'public://new_images/',
        ];
      }
      $image = !empty($images) ? $images : [];
      // Below conditions to set the constant array.
      if (!empty($file) && !empty($image)) {
        $constants = array_merge($file, $image);
      }
      elseif (!empty($file)) {
        $constants = $file;
      }
      elseif (!empty($image)) {
        $constants = $image;
      }
      else {
        $constants = [];
      }
      $constantMultiple = !empty($constants) ? $constants['constants'] = $constants : '';

      // Check condition for taxonomy terms.
      if (!empty($termValue)) {
        foreach ($termValue as $key_array => $key_value) {
          $process[$key_value] = self::taxonomyTermProcess($key_value, $bundleType);
        }
      }

      // Create migration group and it's list.
      if (!empty($data)) {
        $rand = rand();
        if (empty($migration['migration_group'])) {
          $migrationGroup = 'custom_' . $bundleType . '_' . $entityType . '_' . 'import';
          $migration['migration_group'] = $migrationGroup;
        }
        else {
          $migrationGroup = $migration['migration_group'];
        }
        // If group already exists then load it.
        $group = MigrationGroup::load($migration['migration_group']);
        if (empty($group)) {
          // If the specified group does not exist, create it.
          // Provide a little more for the 'default' group.
          $group_properties = [];
          $group_properties['id'] = $migration['migration_group'];
          if ($migration['migration_group'] == $migrationGroup) {
            $group_properties['label'] = 'Custom ' . $bundleType . ' ' . $entityType . ' import';
            $group_properties['description'] = 'A group for import ' . $bundleType . ' ' . $entityType;
            $group_properties['source_type'] = 'Custom CSV';
          }
          else {
            $group_properties['label'] = $group_properties['id'];
            $group_properties['description'] = '';
          }
          // Create function to create migration group.
          $group = MigrationGroup::create($group_properties);
          $group->save();
        }
        $migration = Migration::create(
        [
          'id' => $bundleType . '_' . $entityType . '_' . 'import_' . $rand,
          'label' => $bundleType . ' ' . $entityType . ' ' . 'migration import ' . date("m/d/Y H:i:s"),
          'migration_group' => $migrationGroup,
          'source' => [
            'plugin' => 'csv',
            // New uploaded CSV path.
            'path' => 'public://upload_csv/' . $file_name,
            'file_class' => 'Drupal\migrate_source_csv\CSVFileObject',
            'enclosure' => '"',
            'escape' => '\\',
            'delimiter' => ',',
            'header_row_count' => 1,
            'keys' => [
              '0' => 'id',
            ],
            'file_flags' => \SplFileObject::READ_CSV | \SplFileObject::READ_AHEAD | \SplFileObject::DROP_NEW_LINE | \SplFileObject::SKIP_EMPTY,
            $constantMultiple,
          ],
          'destination' => [
            'plugin' => 'entity:node',
            'default_bundle' => $bundleType,
          ],
          'process' => $process,
          'migration_tags' => [],
          'migration_dependencies' => [],
        ]
        );
        // Save and create migration list.
        $migration->save();

        // Redirect on migration group page.
        $redirect_path = '/admin/structure/migrate/manage/' . $migrationGroup . '/migrations';
        $response = new RedirectResponse($redirect_path);
        return $response->send();
      }
    }
  }

  /**
   * Create new CSV according to refine array.
   *
   * Place new CSV on a specific path.
   */
  public static function coutputCsv($fileName, $assocDataArray) {
    $csv_handler = fopen($fileName, 'w');
    fputcsv($csv_handler, array_keys($assocDataArray['0']));
    foreach ($assocDataArray as $values) {
      fputcsv($csv_handler, $values);
    }
    // Define the folder directory name.
    $directory = 'public://upload_csv/';
    file_prepare_directory($directory, FILE_CREATE_DIRECTORY);
    $host = \Drupal::request()->getSchemeAndHttpHost();
    $file = file_get_contents($host . '/' . $fileName);

    // Place the new CSV file on above directory.
    $file_save = file_save_data($file, $directory . $fileName, FILE_EXISTS_RENAME);

    fclose($csv_handler);
    return $file_save;
  }

  /**
   * Convert CSV to array.
   */
  public static function csvToArray($filename = '', $delimiter = '') {
    if (!file_exists($filename) || !is_readable($filename)) {
      return FALSE;
    }
    $header = NULL;
    $data = [];
    if (($handle = fopen($filename, 'r')) !== FALSE) {
      while (($row = fgetcsv($handle, 1000, $delimiter)) !== FALSE) {
        if (!$header) {
          $header = $row;
        }
        else {
          $data[] = array_combine($header, $row);
        }
      }

      fclose($handle);
    }
    return $data;
  }

  /**
   * {@inheritdoc}
   */
  public static function taxonomyTermProcess($field_name, $bundleType) {
    $type_result = self::FieldTermType($field_name, $bundleType);

    // Confirmt that field is term type.
    if ($type_result['field_type'] == 'entity_reference' && $type_result['entity_type'] == 'taxonomy_term') {
      $field_definition = $type_result['field_definition'];
      $term_machine_name = $field_definition->getSetting('handler_settings')['target_bundles'];
    }
    if (!empty($term_machine_name)) {
      foreach ($term_machine_name as $term_key => $term_value) {
        $machine_name = $term_value;
      }
    }

    // Set the plugin name for term.
    if (!empty($machine_name)) {
      $process = [
        '0' => [
          'plugin' => 'explode',
          'source' => $field_name,
          'delimiter' => ';',
        ],
        '1' => [
          'plugin' => 'entity_generate',
          'entity_type' => 'taxonomy_term',
          'bundle_key' => 'vid',
          'bundle' => $machine_name,
        ],
      ];
    }
    if (!empty($machine_name)) {
      return $process;
    }
    else {
      return;
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function fileProcess($field_name, $bundleType) {
    $type_result = self::FieldTermType($field_name, $bundleType);
    // Confirm check that if fiels is 'file' type.
    if ($type_result['field_type'] == 'file' && $type_result['entity_type'] == 'file') {
      $field_definition = $type_result['field_definition'];
      $machine_name = $field_definition->get('field_name');
    }

    if (!empty($machine_name)) {
      $process = [
        '0' => [
          'plugin' => 'explode',
          'source' => $field_name,
          'delimiter' => ';',
        ],
        '1' => [
          'plugin' => 'file_import',
          'destination' => 'constants/file_destination',
        ],
      ];
      return $process;
    }
    else {
      return;
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function imageProcess($field_name, $bundleType) {
    $type_result = self::FieldTermType($field_name, $bundleType);
    // kint($type_result); die;.
    if ($type_result['field_type'] == 'image' && $type_result['entity_type'] == 'file') {
      $field_definition = $type_result['field_definition'];
      $machine_name = $field_definition->get('field_name');
    }

    if (!empty($machine_name)) {
      $process = [
        '0' => [
          'plugin' => 'explode',
          'source' => $field_name,
          'delimiter' => ';',
            // 'limit' => 2,.
        ],
        '1' => [
          'plugin' => 'image_import',
          'destination' => 'constants/image_destination',
        ],
      ];
      return $process;
    }
    else {
      return [];
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function fieldTermType($field_name, $bundleType) {
    $bundle_fields = \Drupal::getContainer()->get('entity_field.manager')->getFieldDefinitions('node', $bundleType);
    if (!empty($bundle_fields) && !empty($field_name)) {
      $field_definition = $bundle_fields[$field_name];
      if (!empty($field_definition)) {
        $field_type = !empty($field_definition->getType()) ? $field_definition->getType() : '';
        $term_type = !empty($field_definition->getSettings()['target_type']) ? $field_definition->getSettings()['target_type'] : '';

        return [
          'entity_type' => $term_type,
          'field_type'  => $field_type,
          'field_definition' => $field_definition,
        ];
      }
      else {
        return;
      }
    }
    else {
      return;
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function csvHeader($path = '') {
    $file = fopen($path, 'r');
    $headers = fgetcsv($file);

    return $headers;
  }

}
