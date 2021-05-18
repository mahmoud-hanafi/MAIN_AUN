<?php

namespace Drupal\simple_node_importer\Services;

use Drupal\taxonomy\Entity\Vocabulary;
use Drupal\Core\Url;
use Drupal\file\Entity\File;
use Drupal\node\Entity\NodeType;
use Drupal\Core\Config\ConfigFactory;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\node\Entity\Node;
use Drupal\taxonomy\Entity\Term;
use Drupal\user\Entity\User;

/**
 * {@inheritdoc}
 */
class GetServices {

  /**
   * Drupal\Core\Config\ConfigFactory definition.
   *
   * @var Drupal\Core\Config\ConfigFactory
   */
  protected $configFactory;

  /**
   * Constructor.
   *
   * @param Drupal\Core\Config\ConfigFactory $configFactory
   *   Constructs a Drupal\Core\Config\ConfigFactory object.
   */
  public function __construct(ConfigFactory $configFactory) {
    $this->configFactory = $configFactory;
  }

  /**
   * {@inheritdoc}
   */
  public function getContentTypeList() {
    $nodeTypes = NodeType::loadMultiple();
    foreach ($nodeTypes as $key => $value) {
      $content_types[$key] = $value->get('name');
    }

    if (isset($content_types['simple_node'])) {
      unset($content_types['simple_node']);
    }

    return $content_types;
  }

  /**
   * {@inheritdoc}
   */
  public function snpSelectCreateCsv($entity_type, $content_type) {
    $csv = [];
    $type = 'csv';
    if ($entity_type == 'taxonomy') {
      $csv = ['Vocabolary', 'Term1', 'Term2', 'Term3', 'Term4'];
      $filename = $entity_type . '_template.csv';
    }
    else {
      $labelarray = $this->snpGetFieldList($entity_type, $content_type, $type);
      foreach ($labelarray as $value) {
        $csv[] = $value;
      }
      $filename = $content_type . '_template.csv';
    }

    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
    header('Content-Description: File Transfer');
    header("Content-type: text/csv");
    header("Content-Disposition: attachment; filename={$filename}");
    header("Expires: 0");
    header("Pragma: public");
    $fh = @fopen('php://output', 'w');

    // Put the data into the stream.
    fputcsv($fh, $csv);
    fclose($fh);
    // Make sure nothing else is sent, our file is done.
    exit;
  }

  /**
   * {@inheritdoc}
   */
  public function checkAvailablity($nodeType = 'simple_node') {
    $nodeTypes = NodeType::loadMultiple();
    foreach ($nodeTypes as $key => $value) {
      $content_types[$key] = $value->get('name');
    }

    if (isset($content_types['simple_node'])) {
      return TRUE;
    }
    else {
      return FALSE;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function simpleNodeImporterGetAllColumnHeaders($fileuri) {
    $handle = fopen($fileuri, 'r');
    $row = fgetcsv($handle);
    foreach ($row as $value) {
      // code...
      $key = strtolower(preg_replace('/\s+/', '_', $value));
      $column[$key] = $value;
    }
    return $column;
  }

  /**
   * {@inheritdoc}
   */
  public function snpGetFieldList($entity_type = 'node', $content_type = '', $type = NULL) {

    if (!empty($content_type)) {

      $fieldsManager = $this->snpGetFieldsDefinition($entity_type, $content_type);

      $fieldsArr = $this->snpGetFields($fieldsManager, $type, $entity_type);

      return $fieldsArr;
    }
    else {
      return "";
    }
  }

  /**
   * {@inheritdoc}
   */
  public function simpleNodeImporterCreateTaxonomy($nid) {
    $node = Node::load($nid);
    $fid = $node->get('field_upload_csv')->getValue()[0]['target_id'];
    $file = File::load($fid);
    $uri = $file->getFileUri();
    $url = Url::fromUri(file_create_url($uri))->toString();
    $handle = fopen($url, 'r');
    while ($row = fgetcsv($handle)) {
      for ($i = 0; $i <= count($row) - 1; $i++) {

        $name = $row[$i];

        if (empty($name)) {
          break;
        }

        if ($i == 0) {
          $vid = strtolower(preg_replace('/\s+/', '_', $name));
          $vocabularies = Vocabulary::loadMultiple();
          if (!isset($vocabularies[$vid])) {
            $vocabulary = Vocabulary::create([
              'vid' => $vid,
              'description' => '',
              'name' => $name,
            ]);
            $vocabulary->save();
          }
        }
        else {
          $termArray = [
            'name' => $name,
            'vid' => $vid,
          ];
          $termid = \Drupal::entityManager()->getStorage('taxonomy_term')->loadByProperties($termArray);
          if ($i == 1) {
            if (empty($termid)) {
              Term::create($termArray)->save();
            }
          }
          else {
            $parent = $row[$i - 1];
            $termArray = [
              'name' => $parent,
              'vid' => $vid,
            ];
            $termexist = 0;
            $parenttermid = \Drupal::entityManager()->getStorage('taxonomy_term')->loadByProperties($termArray);
            $childterms = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadChildren(key($parenttermid));
            foreach ($childterms as $childterm) {
              if ($childterm->getName() == $name) {
                $termexist = 1;
              }
            }
            if ($termexist == 0) {
              if (!empty($parenttermid)) {
                Term::create([
                  'parent' => key($parenttermid),
                  'name' => $name,
                  'vid' => $vid,
                ])->save();
              }
            }
          }
        }
      }
    }
    drupal_set_message(t('Taxonomies are created successfully'));
  }

  /**
   * {@inheritdoc}
   */
  public function simpleNodeImporterGetPreSelectedValues($form, $headers) {
    foreach ($form['mapping_form'] as $field => $attributes) {
      if (is_array($attributes)) {
        foreach ($attributes['#options'] as $key => $value) {
          if (array_key_exists($key, $headers) && $headers[$key] == $attributes['#title']) {
            $form['mapping_form'][$field]['#default_value'] = $key;
          }
        }
      }
    }
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function snpGetFieldsDefinition($entity_type = 'node', $content_type = '') {
    $entityManager = \Drupal::service('entity_field.manager');
    $fieldsManager = $entityManager->getFieldDefinitions($entity_type, $content_type);
    return $fieldsManager;
  }

  /**
   * {@inheritdoc}
   */
  public function snpGetFields($fieldsManager, $type, $entity_type = NULL) {
    if ($entity_type == 'node') {
      $defaultFieldArr = ['title', 'body', 'status', 'uid'];
    }
    else {
      $defaultFieldArr = ['name', 'mail', 'status', 'roles', 'user_picture'];
    }
    $haystack = 'field_';
    foreach ($fieldsManager as $key => $field) {
      if (in_array($key, $defaultFieldArr) || strpos($key, $haystack) !== FALSE) {
        if ($type == 'csv') {
          if (method_exists($field->getLabel(), 'render')) {
            $fieldsArr[$key] = $field->getLabel()->render();
          }
          else {
            $fieldsArr[$key] = $field->getLabel();
          }
        }
        elseif ($type == 'import') {
          // Fetch the list of required fields.
          if ($fieldsManager[$key]->isRequired()) {
            $fieldsArr['required'][$key] = $key;
          }

          // Fetch the list of multivalued fields.
          if (!in_array($key, $defaultFieldArr)) {
            $fieldStorageConfig = FieldStorageConfig::loadByName($entity_type, $key);
            if ($fieldStorageConfig->getCardinality() === -1 || $fieldStorageConfig->getCardinality() > 1) {
              $fieldsArr['multivalued'][$key] = $key;
            }
          }
        }
        elseif ($type == 'mapping') {
          $fieldsArr[$key] = $field;
        }
      }
    }
    return $fieldsArr;
  }

  /**
   * Checks the widget type of each field.
   */
  public function checkFieldWidget($field_names, $data, $node, $entity_type) {
    $excludeFieldArr = [
      'name',
      'mail',
      'status',
      'roles',
      'nid',
      'type',
      'uid',
      'title',
    ];

    $flag = TRUE;
    foreach ($field_names as $field_machine_name) {
      if (!in_array($field_machine_name, $excludeFieldArr)) {
        $field_info = FieldStorageConfig::loadByName($entity_type, $field_machine_name);
        $entityManager = \Drupal::service('entity_field.manager');
        $field_definition = $entityManager->getFieldDefinitions($entity_type, $data['type']);
        $fieldStorageDefinition = $entityManager->getFieldStorageDefinitions($entity_type, $data['type']);

        $fieldProperties = $field_definition[$field_machine_name];
        $fieldType = $field_info->getType();
        $fieldIsRequired = $fieldProperties->isRequired();

        if ($fieldType == 'entity_reference') {
          $fieldSetting = $field_info->getSetting('target_type');
        }
        elseif ($fieldType == 'datetime') {
          $fieldSetting = $field_info->getSetting('datetime_type');
        }
        else {
          $fieldSetting = NULL;
        }
        $dataValidated = $this->getFieldValidation($fieldType, $data[$field_machine_name], $fieldIsRequired);
        if ($dataValidated) {
          switch ($fieldType) {
            case 'email':
              $node[$field_machine_name] = $this->buildNodeData($data[$field_machine_name], $fieldType);
              break;

            case 'image':
            case 'file':
              $node[$field_machine_name] = $this->buildNodeData($data[$field_machine_name], $fieldType);
              break;

            case 'entity_reference':
              if (!empty($data[$field_machine_name])) {
                $preparedData = $this->prepareEntityReferenceFieldData($field_definition, $field_machine_name, $data, $node, $fieldSetting);
                if ($preparedData === FALSE) {
                  $flag = FALSE;
                  break;
                }
                else {
                  $node[$field_machine_name] = $preparedData;
                }
              }
              break;

            case 'text':
            case 'string':
            case 'string_long':
            case 'text_long':
            case 'text_with_summary':
              $node[$field_machine_name] = $this->buildNodeData($data[$field_machine_name], $fieldType);
              break;

            case 'boolean':
              $node[$field_machine_name] = ($data[$field_machine_name] == 1) ? $data[$field_machine_name] : ((strtolower($data[$field_machine_name]) == 'y') ? 1 : 0);
              break;

            case 'datetime':
              $dateValue = $this->buildNodeData($data[$field_machine_name], $fieldType, $fieldSetting);
              if ($dateValue) {
                $node[$field_machine_name] = $dateValue;
              }
              elseif ($dateValue === FALSE) {
                $flag = FALSE;
                break;
              }
              break;

            case 'number_integer':
            case 'number_float':
            case 'link':
              $node[$field_machine_name] = $this->buildNodeData($data[$field_machine_name], $fieldType, $fieldSetting);
              break;

            case 'list_text':
            case 'list_string':
            case 'list_float':
            case 'list_integer':
              $allowed_values = options_allowed_values($fieldStorageDefinition[$field_machine_name]);
              if (is_array($data[$field_machine_name])) {
                foreach ($data[$field_machine_name] as $k => $field_value) {
                  $key_value = array_search($field_value, $allowed_values, TRUE);
                  if ($key_value) {
                    $node[$field_machine_name][$k]['value'] = $key_value;
                  }
                  else {
                    $flag = FALSE;
                    break;
                  }
                }
              }
              else {
                $key_value = array_search(strtolower($data[$field_machine_name]), array_map('strtolower', $allowed_values));
                if ($key_value) {
                  $node[$field_machine_name][0]['value'] = $key_value;
                }
                elseif (!empty($data[$field_machine_name])) {
                  $flag = FALSE;
                  break;
                }
              }
              break;
          }
          // End of switch case.
        }
        else {
          $flag = FALSE;
          break;
        }
      }// end of 1st if
    }// end of foreach
    if ($flag === FALSE) {
      $node = [];
      $node['result'] = $data;
      return $node;
    }
    else {
      return $node;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getUserByEmail($email, $op = NULL) {
    // Load user object
    // $op could be 'new', 'admin', 'current', 'content_validate'.
    $userObj = user_load_by_mail($email);

    if ($userObj) {
      return $userObj;
    }
    elseif ($op == 'new') {
      return $this->createNewUser($email);
    }
    elseif ($op == 'admin') {
      $adminUid = 1;
      return $adminUid;
    }
    elseif ($op == 'current') {
      $userObj = \Drupal::currentUser();
      return $userObj;
    }
    elseif ($op == 'content_validate') {
      return NULL;
    }

  }

  /**
   * {@inheritdoc}
   */
  public function createNewUser(string $email = NULL, string $uname = NULL) {

    if (!empty($email)) {
      $today = date('dmy');
      $username = explode('@', $email);
      $userId = $this->getUserByUsername($username[0]);
      if ($userId && is_int($userId)) {
        $uname = $username . $today;
      }
      else {
        $uname = $username;
      }
    }
    elseif (!empty($uname)) {
      $email = '';
    }

    $user = User::create([

      'name' => $uname,

      'mail' => $email,

      'pass' => user_password(10),

      'status' => 1,

      'roles'  => ['authenticated'],

    ]);

    return $user->save();
  }

  /**
   * {@inheritdoc}
   */
  public function getUserByUsername(string $uname, $op = NULL) {

    // $op could be 'new', 'admin', 'current', 'content_validate'.
    $userId = \Drupal::entityQuery('user')
      ->condition('name', $uname)
      ->range(0, 1)
      ->execute();

    if (!empty($userId)) {
      return key($userId);
    }
    elseif ($op == 'new') {
      return $this->createNewUser(NULL, $uname);
    }
    elseif ($op == 'admin') {
      $adminUid = 1;
      return $adminUid;
    }
    elseif ($op == 'current') {
      $userObj = \Drupal::currentUser();
      return $userObj;
    }
    elseif ($op == 'content_validate') {
      return NULL;
    }
    else {
      return NULL;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function prepareEntityReferenceFieldData($field_definition, $field_machine_name, $data, $node, $fieldSetting) {
    $flag = TRUE;
    $dataRow = [];
    if ($fieldSetting == 'taxonomy_term') {
      $handler_settings = $field_definition[$field_machine_name]->getSetting('handler_settings');
      $target_bundles = $handler_settings['target_bundles'];
      $vocabulary_name = (is_array($target_bundles) && count($target_bundles) > 1) ? $target_bundles : key($target_bundles);
      $allw_term = \Drupal::config('simple_node_importer.settings')->get('simple_node_importer_allow_add_term');

      // Code for taxonomy data handling.
      if ((is_array($vocabulary_name) && count($vocabulary_name) > 1) || empty($data[$field_machine_name])) {
        return $flag = FALSE;
      }
      else {

        if (is_array($data[$field_machine_name])) {

          foreach ($data[$field_machine_name] as $k => $term_name) {
            if ($term_name) {
              $termArray = [
                'name' => $term_name,
                'vid' => $vocabulary_name,
              ];

              $taxos_obj = \Drupal::entityManager()->getStorage('taxonomy_term')->loadByProperties($termArray);
              $termKey = key($taxos_obj);
              if (!$taxos_obj && $allw_term) {

                $term = Term::create([
                  'vid' => $vocabulary_name,
                  'name' => $term_name,
                ]);

                $term->enforceIsNew();
                $term->save();

                $dataRow[$k]['target_id'] = $term->id();
              }
              else {
                $termObj = $taxos_obj[$termKey];
                $tid = $termObj->id();
                $dataRow[$k]['target_id'] = $tid;
              }
            }
          }
        }
        else {
          $termArray = [
            'name' => $data[$field_machine_name],
            'vid' => $vocabulary_name,
          ];

          $taxos_obj = \Drupal::entityManager()->getStorage('taxonomy_term')->loadByProperties($termArray);
          $termKey = key($taxos_obj);

          if (!$taxos_obj && $allw_term) {
            $term = Term::create([
              'vid' => $vocabulary_name,
              'name' => $data[$field_machine_name],
            ]);

            $term->enforceIsNew();
            $term->save();
            $dataRow[0]['target_id'] = $term->id();
          }
          else {
            $termObj = $taxos_obj[$termKey];
            $tid = $termObj->id();
            $dataRow[0]['target_id'] = $tid;
          }
        }
      }
      return $dataRow;
    }

    if ($fieldSetting == 'user') {
      $userEmail = $data[$field_machine_name];
      if (is_array($userEmail)) {
        foreach ($userEmail as $email) {
          if ($email) {
            $flag = $this->getFieldValidation('email', $email);
            if ($flag) {
              $user = $this->getUserByEmail($email, 'content_validate');
              if ($user && !is_int($user)) {
                $dataRow[] = $user->id();
              }
              else {
                return $flag = FALSE;
              }
            }
            else {
              $uid = $this->getUserByUsername($email, 'content_validate');
              if ($uid) {
                $dataRow[] = $uid;
              }
              else {
                return $flag = FALSE;
              }
            }
          }
        }
      }
      elseif ($userEmail) {
        $flag = $this->getFieldValidation('email', $userEmail);
        if ($flag) {
          $user = $this->getUserByEmail($userEmail, 'content_validate');
          if ($user && !is_int($user)) {
            $dataRow = $user->id();
          }
          else {
            return $flag = FALSE;
          }
        }
        else {
          $uid = $this->getUserByUsername($userEmail, 'content_validate');
          if ($uid) {
            $dataRow = $uid;
          }
          else {
            return $flag = FALSE;
          }
        }
      }
      return $dataRow;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getFieldValidation($fieldType, $field_data, $fieldIsRequired = FALSE) {
    $flag = TRUE;
    // $k = 0;.
    if (is_array($field_data) && $fieldIsRequired == TRUE) {
      foreach ($field_data as $key => $fieldVal) {
        $flags[$key] = empty($fieldVal) ? FALSE : TRUE;
      }

      if (!in_array(TRUE, $flags)) {
        return $flag = FALSE;
      }
    }

    if (empty($field_data) && $fieldIsRequired == TRUE) {
      return $flag = FALSE;
    }
    elseif (!empty($field_data)) {
      switch ($fieldType) {
        case 'email':
          $flg = 0;
          if (is_array($field_data)) {
            foreach ($field_data as $fieldData) {
              if (!empty($fieldData) && filter_var($fieldData, FILTER_VALIDATE_EMAIL) != FALSE) {
                $flg = 1;
              }
              elseif (!empty($fieldData)) {
                return $flag = FALSE;
              }
            }
            $flag = (($flg == 0) && $fieldIsRequired == TRUE) ? FALSE : TRUE;
          }
          else {
            if (!empty($field_data) && filter_var($field_data, FILTER_VALIDATE_EMAIL) != FALSE) {
              $flag = TRUE;
            }
            elseif (empty($field_data) && $fieldIsRequired == FALSE) {
              $flag = TRUE;
            }
            else {
              $flag = FALSE;
            }
          }
          break;

        case 'image':
        case 'link':
          $flg = 0;
          if (is_array($field_data)) {
            foreach ($field_data as $fieldData) {
              if (!empty($fieldData) && filter_var($fieldData, FILTER_VALIDATE_URL) != FALSE) {
                $flg = 1;
              }
              elseif (!empty($fieldData)) {
                return $flag = FALSE;
              }
            }
            $flag = (($flg == 0) && $fieldIsRequired == TRUE) ? FALSE : TRUE;
          }
          else {
            if (!empty($field_data) && filter_var($field_data, FILTER_VALIDATE_URL) != FALSE) {
              $flag = TRUE;
            }
            elseif (empty($field_data) && $fieldIsRequired == FALSE) {
              $flag = TRUE;
            }
            else {
              $flag = FALSE;
            }
          }
          break;
      }
    }
    return $flag;
  }

  /**
   * {@inheritdoc}
   */
  public function buildNodeData($data, $fieldType, $fieldSetting = NULL) {
    $i = 0;
    $fieldTypes = ['number_integer', 'number_float'];
    $textFieldTypes = ['string_long', 'string'];
    // $dateformat = ($fieldSetting == 'datetime') ? 'Y-m-d\TH:i:s' : 'Y-m-d';.
    $dataRow = [];

    if (is_array($data) && !empty($data)) {
      foreach ($data as $value) {
        if (in_array($fieldType, ['image', 'file']) && !empty($value) && filter_var($value, FILTER_VALIDATE_URL)) {
          // Code for image/file field..
          $file = system_retrieve_file($value, NULL, TRUE, FILE_EXISTS_REPLACE);
          $dataRow[$i]['target_id'] = !empty($file) ? $file->id() : NULL;
        }
        elseif ($fieldType == 'datetime') {
          if (!empty($value)) {
            if ($this->validateDateExpression($value)) {
              $dataRow[$i]['value'] = ($fieldSetting == 'datetime') ? date_format(date_create($value), 'Y-m-d\TH:i:s') : date_format(date_create($value), 'Y-m-d');
            }
            else {
              return FALSE;
            }
          }
        }
        elseif (in_array($fieldType, $fieldTypes)) {
          $dataRow[$i]['value'] = $value;
        }
        elseif ($fieldType == 'link') {
          $dataRow[$i]['uri'] = $value;
        }
        elseif (in_array($fieldType, $textFieldTypes)) {
          $dataRow[$i]['value'] = utf8_encode(trim($value));
        }
        else {
          // code...
          $dataRow[$i] = trim($value);
        }
        $i++;
      }

    }
    elseif (!empty($data)) {
      if (in_array($fieldType, ['image', 'file']) && !empty($data) && filter_var($data, FILTER_VALIDATE_URL)) {
        // Code for image/file field..
        $file = system_retrieve_file($data, NULL, TRUE, FILE_EXISTS_REPLACE);
        $dataRow[0]['target_id'] = !empty($file) ? $file->id() : NULL;
      }
      elseif ($fieldType == 'datetime') {
        if ($this->validateDateExpression($data)) {
          $dataRow['value'] = ($fieldSetting == 'datetime') ? date_format(date_create($data), 'Y-m-d\TH:i:s') : date_format(date_create($data), 'Y-m-d');
        }
        else {
          return FALSE;
        }
      }
      elseif (in_array($fieldType, $fieldTypes)) {
        $dataRow[0]['value'] = $data;
      }
      elseif ($fieldType == 'link') {
        $dataRow['uri'] = $data;
      }
      elseif (in_array($fieldType, $textFieldTypes)) {
        $dataRow[0]['value'] = utf8_encode(trim($data));
      }
      else {
        // code...
        $dataRow = trim($data);
      }
    }
    return $dataRow;
  }

  /**
   * Function to generate random strings.
   *
   * @param int $length
   *   Number of characters in the generated string.
   *
   * @return string
   *   A new string is created with random characters of the desired length.
   */
  public function generateReference(int $length = 10) {
    srand();
    $string = "";
    $chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
    for ($i = 0; $i < $length; $i++) {
      $string .= substr($chars, rand(0, strlen($chars)), 1);
    }
    return $string;
  }

  /**
   * {@inheritdoc}
   */
  public static function getImageFid($fileUrl) {
    // Code for image/file field..
    $file = system_retrieve_file($fileUrl, NULL, TRUE, FILE_EXISTS_REPLACE);
    $fid = !empty($file) ? $file->id() : NULL;
    return $fid;
  }

  /**
   * {@inheritdoc}
   */
  public static function generateFieldSetValue($fieldKey, $fieldVal, $fieldWidget, $entity_type, $bundle) {
    $excludeFieldArr = [
      'type',
      'nid',
      'uid',
      'title',
      'reference',
      'status',
      'name',
      'mail',
      'roles',
    ];

    $flag = TRUE;
    $key = 0;
    if (!in_array($fieldKey, $excludeFieldArr)) {
      $getFieldInfo = GetServices::getFieldInfo($entity_type, $fieldKey, $bundle);
      $fieldType = $getFieldInfo['fieldType'];
      $fieldIsRequired = $getFieldInfo['fieldIsRequired'];
      $fieldCardinality = $getFieldInfo['fieldCardinality'];

      if (empty($fieldVal) && $fieldIsRequired) {
        $fields[] = $fieldKey;
      }

      switch ($fieldType) {
        case 'text_with_summary':
          // code...
          if ($fieldIsRequired && empty($fieldVal)) {
            $fields[] = $fieldKey;
          }
          else {
            $fieldWidget[0]['#default_value'] = $fieldVal;
          }
          break;

        case 'list_float':
        case 'list_integer':
        case 'list_string':
          if (!empty($fieldVal) && ($fieldCardinality == -1 || $fieldCardinality > 1) && is_array($fieldVal)) {
            $flag = 0;
            foreach ($fieldVal as $value) {
              // code...
              if (!empty($value)) {
                $flag = 1;
                $fieldWidget['#default_value'][] = $value;
              }
            }
            if ($fieldIsRequired && $flag == 0) {
              $fields[] = $fieldKey;
            }
          }
          elseif (!empty($fieldVal)) {
            $fieldWidget['#default_value'] = $fieldVal;
          }
          elseif ($fieldIsRequired && empty($fieldVal)) {
            $fields[] = $fieldKey;
          }
          break;

        case 'boolean':
          // code...
          if ($fieldIsRequired && empty($fieldVal)) {
            $fields[] = $fieldKey;
          }
          else {
            $fieldWidget['value']['#default_value'] = $fieldVal;
          }
          break;

        case 'entity_reference':
          // code...
          if ($fieldWidget[0]['target_id']['#selection_settings']['target_bundles']) {
            $target_bundle = key($fieldWidget[0]['target_id']['#selection_settings']['target_bundles']);
          }
          else {
            $target_bundle = '';
          }
          $target_type = $fieldWidget[0]['target_id']['#target_type'];

          if ($target_type == "taxonomy_term") {
            $flag = 0;
            if (is_array($fieldVal) && !empty($fieldVal)) {
              foreach ($fieldVal as $termName) {
                if ($termName) {
                  // code...
                  $flag = 1;
                  $termArray = [
                    'name' => $termName,
                    'vid' => $target_bundle,
                  ];

                  $taxos_obj = \Drupal::entityManager()->getStorage('taxonomy_term')->loadByProperties($termArray);
                  $refObject[] = key($taxos_obj);
                }
              }
              if ($flag == 0 && $fieldIsRequired) {
                $fields[] = $fieldKey;
              }
            }
            elseif (!empty($fieldVal)) {
              // code...
              $termArray = [
                'name' => $fieldVal,
                'vid' => $target_bundle,
              ];

              $taxos_obj = \Drupal::entityManager()->getStorage('taxonomy_term')->loadByProperties($termArray);
              $refObject = key($taxos_obj);
            }
            elseif ($fieldIsRequired && empty($refObject)) {
              $fields[] = $fieldKey;
            }
          }
          elseif ($target_type == "user") {
            $flag = 0;
            if (is_array($fieldVal) && !empty($fieldVal)) {
              foreach ($fieldVal as $userData) {
                if (filter_var($userData, FILTER_VALIDATE_EMAIL) && !empty($userData)) {
                  // code...
                  $user = user_load_by_mail($userData);
                  if (!empty($user)) {
                    $userObject[] = $user->id();
                    $flag = 1;
                  }
                }
                elseif (!empty($userData)) {
                  $user = user_load_by_name($userData);
                  if (!empty($user)) {
                    $userObject[] = $user->id();
                    $flag = 1;
                  }
                }
                else {
                  $fields[] = $fieldKey;
                }
              }
              if ($flag == 0 && $fieldIsRequired) {
                $fields[] = $fieldKey;
              }
            }
            elseif (!empty($fieldVal)) {
              if (filter_var($fieldVal, FILTER_VALIDATE_EMAIL)) {
                // code...
                $user = user_load_by_mail($fieldVal);
                if (!empty($user)) {
                  $userObject = $user->id();
                }
              }
              else {
                $user = user_load_by_name($fieldVal);
                if ($user != FALSE) {
                  $userObject[] = $user->id();
                }
                else {
                  $fields[] = $fieldKey;
                }
              }
            }
            if ($fieldIsRequired && !empty($fieldVal)) {
              $fields[] = $fieldKey;
            }
          }

          if (!empty($refObject) && ($fieldCardinality == -1 || $fieldCardinality > 1) && $target_type == "taxonomy_term") {
            foreach ($refObject as $refVal) {
              // code...
              $fieldWidget[$key] = $fieldWidget[0];
              $fieldWidget[$key++]['target_id']['#default_value'] = Term::load($refVal);
            }
          }
          elseif (!empty($refObject)) {
            $fieldWidget[0]['target_id']['#default_value'] = Term::load($refObject);
          }

          if (!empty($userObject) && ($fieldCardinality == -1 || $fieldCardinality > 1) && $target_type == "user") {
            foreach ($userObject as $userVal) {
              // code...
              $fieldWidget[$key] = $fieldWidget[0];
              $fieldWidget[$key++]['target_id']['#default_value'] = User::load($userVal);
            }
          }
          elseif (!empty($userObject)) {
            $fieldWidget[0]['target_id']['#default_value'] = User::load($userObject);
          }
          break;

        case 'datetime':
          // code...
          $dateFormat = $fieldWidget[0]['value']['#date_date_format'];
          $timeFormat = $fieldWidget[0]['value']['#date_time_format'];
          if (!empty($fieldVal) && ($fieldCardinality == -1 || $fieldCardinality > 1) && is_array($fieldVal)) {
            $flag = 0;
            foreach ($fieldVal as $date) {
              if (!empty($date)) {
                if (GetServices::validateDateExpression($date) && !empty($dateFormat) && !empty($timeFormat)) {
                  $date = date_create($date);
                  $dateTime = DrupalDateTime::createFromDateTime($date);
                  $fieldWidget[0]['value']['#default_value'] = $dateTime;
                  $flag = 1;
                }
                elseif (GetServices::validateDateExpression($date) && !empty($dateFormat) && empty($timeFormat)) {
                  $date = date_create($date);
                  $dateTime = DrupalDateTime::createFromDateTime($date);
                  $fieldWidget[0]['value']['#default_value'] = $dateTime;
                  $flag = 1;
                }
                else {
                  $fields[] = $fieldKey;
                }
              }
            }

            if ($flag == 0 && $fieldIsRequired) {
              $fields[] = $fieldKey;
            }
          }
          elseif (!empty($fieldVal)) {
            if (GetServices::validateDateExpression($fieldVal) && !empty($dateFormat) && !empty($timeFormat)) {
              $date = date_create($fieldVal);
              $dateTime = DrupalDateTime::createFromDateTime($date);
              $fieldWidget[0]['value']['#default_value'] = $dateTime;
            }
            elseif (GetServices::validateDateExpression($fieldVal) && !empty($dateFormat) && empty($timeFormat)) {
              $date = date_create($fieldVal);
              $dateTime = DrupalDateTime::createFromDateTime($date);
              $fieldWidget[0]['value']['#default_value'] = $dateTime;
            }
            else {
              $fields[] = $fieldKey;
            }
          }
          elseif ($flag == 0 && $fieldIsRequired) {
            $fields[] = $fieldKey;
          }
          break;

        case 'string':
          $flag = 0;
          if (!empty($fieldVal) && ($fieldCardinality == -1 || $fieldCardinality > 1) && is_array($fieldVal)) {
            foreach ($fieldVal as $value) {
              // code...
              if (!empty($value)) {
                $fieldWidget[$key] = $fieldWidget[0];
                $fieldWidget[$key++]['value']['#default_value'][] = $value;
                $flag = 1;
              }
            }
            if ($flag == 0 && $fieldIsRequired) {
              $fields[] = $fieldKey;
            }
          }
          elseif (!empty($fieldVal)) {
            $fieldWidget[0]['value']['#default_value'] = $fieldVal;
          }
          elseif ($fieldIsRequired && empty($fieldVal)) {
            $fields[] = $fieldKey;
          }
          break;

        case 'file':
        case 'image':
          // code...
          $flag = 0;
          if (!empty($fieldVal) && ($fieldCardinality == -1 || $fieldCardinality > 1) && is_array($fieldVal)) {
            foreach ($fieldVal as $file) {
              // code...
              if (!empty($file)) {
                if (filter_var($file, FILTER_VALIDATE_URL)) {
                  $fid = GetServices::getImageFid($file);
                  if (!empty($fid)) {
                    $fieldWidget[$key] = $fieldWidget[0];
                    $fieldWidget[$key++]['#default_value']['fids'][] = $fid;
                    $flag = 1;
                  }
                  else {
                    $fields[] = $fieldKey;
                  }
                }
                else {
                  $fields[] = $fieldKey;
                }
              }
            }
            if ($flag == 0 && $fieldIsRequired) {
              $fields[] = $fieldKey;
            }

          }
          elseif (!empty($fieldVal)) {
            if (filter_var($fieldVal, FILTER_VALIDATE_URL)) {
              $fid = GetServices::getImageFid($fieldVal);
              if (!empty($fid)) {
                $fieldWidget[0]['#default_value']['fids'] = [$fid];
              }
            }
            else {
              $fields[] = $fieldKey;
            }
          }
          elseif ($fieldIsRequired && empty($fieldVal)) {
            $fields[] = $fieldKey;
          }
          break;

        case 'email':
          $flag = 0;
          if (!empty($fieldVal) && ($fieldCardinality == -1 || $fieldCardinality > 1) && is_array($fieldVal)) {
            foreach ($fieldVal as $email) {
              if ($email != '') {
                if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                  $flag = 1;
                  $fieldWidget[$key] = $fieldWidget[0];
                  $fieldWidget[$key++]['value']['#default_value'] = $email;
                }
                else {
                  $fields[] = $fieldKey;
                }
              }
            }
            if ($flag == 0 && $fieldIsRequired) {
              $fields[] = $fieldKey;
            }

          }
          elseif (!empty($fieldVal)) {
            if (filter_var($fieldVal, FILTER_VALIDATE_EMAIL)) {
              // code..
              $fieldWidget[0]['value']['#default_value'] = $fieldVal;
            }
            else {
              $fields[] = $fieldKey;
            }
          }
          elseif ($fieldIsRequired && empty($fieldVal)) {
            $fields[] = $fieldKey;
          }
          break;

        case 'link':
          $flag = 0;
          if (!empty($fieldVal) && ($fieldCardinality == -1 || $fieldCardinality > 1) && is_array($fieldVal)) {
            foreach ($fieldVal as $link) {
              if (!empty($link) && filter_var($link, FILTER_VALIDATE_URL)) {
                // code..
                $flag = 1;
                $fieldWidget[$key] = $fieldWidget[0];
                $fieldWidget[$key++]['uri']['#default_value'][] = $link;
              }
            }
            if ($flag == 0 && $fieldIsRequired) {
              $fields[] = $fieldKey;
            }
          }
          elseif (!empty($fieldVal)) {
            if (!empty($fieldVal) && filter_var($fieldVal, FILTER_VALIDATE_URL)) {
              // code..
              $fieldWidget[0]['uri']['#default_value'] = $fieldVal;
            }
          }
          elseif ($fieldIsRequired && empty($fieldVal)) {
            $fields[] = $fieldKey;
          }
          break;
      }
    }
    else {
      if ($fieldKey == 'title' && !empty($fieldVal)) {
        $fieldWidget[0]['value']['#default_value'] = $fieldVal;
      }
      elseif ($fieldKey == 'title' && empty($fieldVal)) {
        $fields[] = $fieldKey;
      }
      // For user bundle type.
      if (in_array($fieldKey, ['name', 'mail', 'roles'])) {
        if ($fieldKey == 'name' && empty($fieldVal)) {
          $fields[] = $fieldKey;
        }
        else {
          $fieldWidget['#default_value'] = $fieldVal;
        }
      }

      if ($fieldKey == 'uid') {
        if (!empty($fieldVal)) {
          $uname = user_load_by_name($fieldVal);
          $umail = user_load_by_mail($fieldVal);
          if ($uname != FALSE || $umail != FALSE) {
            $user = ($uname != FALSE) ? $uname : $umail;
            $fieldWidget[0]['target_id']['#default_value'] = User::load($user->id());
          }
          else {
            $fields[] = $fieldKey;
          }
        }
        elseif (empty($fieldVal)) {
          $fields[] = $fieldKey;
        }
      }
    }

    if (!empty($fields)) {
      $result['fieldWidget'] = $fieldWidget;
      $result['bugField'] = $fields;
      return $result;
    }
    else {
      return $fieldWidget;
    }

  }

  /**
   * {@inheritdoc}
   */
  public static function getFieldInfo($entity_type, $fieldKey, $bundle) {

    $field_info = FieldStorageConfig::loadByName($entity_type, $fieldKey);

    $entityManager = \Drupal::service('entity_field.manager');
    $field_definition = $entityManager->getFieldDefinitions($entity_type, $bundle);
    $fieldProperties = $field_definition[$fieldKey];

    $fieldLabel = $field_info->getLabel();
    $fieldType = $field_info->getType();

    $fieldTypeProvider = $field_info->getTypeProvider();

    $fieldCardinality = $field_info->getCardinality();
    $fieldIsRequired = $fieldProperties->isRequired();

    $fieldInfoArray = [
      'fieldLabel' => $fieldLabel,
      'fieldType' => $fieldType,
      'fieldTypeProvider' => $fieldTypeProvider,
      'fieldCardinality' => $fieldCardinality,
      'fieldIsRequired' => $fieldIsRequired,
    ];

    return $fieldInfoArray;
  }

  /**
   * {@inheritdoc}
   */
  public function validateDate($date, $format = 'Y-m-d H:i:s') {
    if ($this->validateDateExpression($date)) {
      $d = DrupalDateTime::createFromFormat($format, $date);
      return $d && $d->format($format) == $date;
    }
    else {
      return FALSE;
    }

  }

  /**
   * {@inheritdoc}
   */
  public function validateDateExpression($date) {
    $regExp = "#^[0-9ampAMP :/-]+$#";
    if (preg_match($regExp, $date)) {
      return TRUE;
    }
    else {
      return FALSE;
    }
  }

}
