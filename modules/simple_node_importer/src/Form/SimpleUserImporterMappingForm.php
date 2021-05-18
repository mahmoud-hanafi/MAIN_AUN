<?php

namespace Drupal\simple_node_importer\Form;

use Drupal\field\Entity\FieldStorageConfig;
use Drupal\file\Entity\File;
use Drupal\Core\Form\FormBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\node\NodeInterface;
use Drupal\user\PrivateTempStoreFactory;
use Drupal\file\FileUsage\FileUsageInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\simple_node_importer\Services\GetServices;

/**
 * Flexible Mapping Form for the Simple Node Importer.
 */
class SimpleUserImporterMappingForm extends FormBase {
  protected $tempStore;
  protected $services;
  protected $fileUsage;
  protected $logger;

  /**
   * Constructs a Drupal\Component\Plugin\PluginBase object.
   *
   * @param Drupal\simple_node_importer\Services\GetServices $getServices
   *   Constructs a Drupal\simple_node_importer\Services object.
   * @param Drupal\Core\TempStore\PrivateTempStoreFactory $sessionVariable
   *   Constructs a Drupal\Core\TempStore\PrivateTempStoreFactory object.
   * @param Drupal\file\FileUsage\FileUsageInterface $file_usage
   *   Constructs a Drupal\file\FileUsage\FileUsageInterface object.
   * @param Drupal\Core\Logger\LoggerChannelFactoryInterface $logger
   *   Constructs a Drupal\Core\Logger\LoggerChannelFactoryInterface object.
   */
  public function __construct(GetServices $getServices, PrivateTempStoreFactory $sessionVariable, FileUsageInterface $file_usage, LoggerChannelFactoryInterface $logger) {
    $this->services = $getServices;
    $this->tempStore = $sessionVariable->get('simple_node_importer');
    $this->fileUsage = $file_usage;
    $this->logger = $logger;
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'simple_node_importer_mapping_form';
  }

  /**
   * Build Flexible Mapping UI form.
   */
  public function buildForm(array $form, FormStateInterface $form_state, $option = NULL, NodeInterface $node = NULL) {
    global $base_url;
    $type = 'module';
    $module = 'simple_node_importer';
    $filepath = $base_url . '/' . drupal_get_path($type, $module) . '/css/files/mapping.png';
    $fid = $node->get('field_upload_csv')->getValue()[0]['target_id'];
    $file = File::load($fid);
    $uri = $file->getFileUri();

    if (empty($node)) {
      $type = 'Simple Node Importer';
      $message = 'Node object is not valid.';
      $this->logger->get($type)->error($message, []);
    }
    elseif ($this->tempStore->get('file_upload_session') == FALSE) {
      $response = new RedirectResponse('/node/add/simple-node');
      $response->send();
    }
    else {
      // Options to be listed in File Column List.
      $headers = $this->services->simpleNodeImporterGetAllColumnHeaders($uri);
      $entity_type = $option;
      $selected_option = $option;
      $type = 'mapping';
      $get_field_list = $this->services->snpGetFieldList($entity_type, $selected_option, $type);
      $parameters = ['option' => $option, 'node' => $node->id()];
      $this->tempStore->set('parameters', $parameters);
      // Add HelpText to the mapping form.
      $form['helptext'] = [
        '#theme' => 'mapping_help_text_info',
        '#fields' => [
          'filepath' => $filepath,
        ],

      ];
      // Add theme table to the mapping form.
      $form['mapping_form']['#theme'] = 'simple_node_import_table';
      // Mapping form.
      $defaultFieldArr = ['name', 'mail', 'status', 'roles'];
      foreach ($get_field_list as $key => $field) {
        if ($entity_type == 'user') {
          $field_name = $field->getName();
          if (in_array($key, $defaultFieldArr)) {
            $field_label = $field->getLabel()->render();
            $fieldcardinality = $field->getCardinality();
          }
          else {
            $field_info = FieldStorageConfig::loadByName($entity_type, $field_name);
            $fieldcardinality = $field_info->get('cardinality');
            $field_label = $field->getLabel();
          }
        }
        if ($fieldcardinality == -1 || $fieldcardinality > 1) {
          $form['mapping_form'][$key] = [
            '#type' => 'select',
            '#title' => $field_label,
            '#options' => $headers,
            '#multiple' => TRUE,
            '#required' => ($field->isRequired()) ? TRUE : FALSE,
            '#empty_option' => $this->t('Select'),
            '#empty_value' => '',
          ];
        }
        else {
          $form['mapping_form'][$key] = [
            '#type' => 'select',
            '#title' => $field_label,
            '#options' => $headers,
            '#required' => ($field->isRequired()) ? TRUE : FALSE,
            '#empty_option' => $this->t('Select'),
            '#empty_value' => '',
          ];
        }
      }

      // Get the preselected values for form fields.
      $form = $this->services->simpleNodeImporterGetPreSelectedValues($form, $headers);

      $form['import'] = [
        '#type' => 'submit',
        '#value' => $this->t('Import'),
        '#weight' => 49,
      ];
      $form['cancel'] = [
        '#type' => 'submit',
        '#value' => $this->t('cancel'),
        '#weight' => 3,
        '#submit' => ['::snpRedirectToCancel'],
      ];
      return $form;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $valarray = [];
    $duplicates = [];
    $count_array = [];
    $form_state->cleanValues();
    foreach ($form_state->getValues() as $key => $val) {
      if ($val && is_array($val)) {
        foreach ($val as $v) {
          $valarray[] = $v;
        }
      }
      elseif ($val) {
        $valarray[] = $val;
      }
    }
    $count_array = array_count_values($valarray);
    foreach ($count_array as $field => $count) {
      if ($count > 1) {
        $duplicates[] = $field;
      }
    }
    foreach ($duplicates as $duplicate_val) {
      foreach ($form_state->getValues() as $key => $val) {
        if ($val == $duplicate_val) {
          $form_state->setErrorByName($key, $this->t('Duplicate Mapping detected for %duplval', [
            '%duplval' => $duplicate_val,
          ]));
        }
        elseif (is_array($val)) {
          foreach ($val as $v) {
            if ($v == $duplicate_val) {
              $form_state->setErrorByName($key, $this->t('Duplicate Mapping detected for %duplval', [
                '%duplval' => $duplicate_val,
              ]));
            }
          }
        }
      }
    }
    // Remove Duplicate Error Messages.
    if (isset($_SESSION['messages']['error'])) {
      $_SESSION['messages']['error'] = array_unique($_SESSION['messages']['error']);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $haystack = 'snp_';
    foreach ($form_state->getValues() as $key => $val) {
      if (strpos($key, $haystack) === FALSE) {
        $mapvalues[$key] = $val;
      }
    }
    $this->tempStore->set('mapvalues', $mapvalues);
    // Remove unnecessary values.
    $parameters = $this->tempStore->get('parameters');
    $form_state->cleanValues();
    $form_state->setRedirect('simple_node_importer.user_confirmation_form', $parameters);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
     $container->get('snp.get_services'),
     $container->get('user.private_tempstore'),
     $container->get('file.usage'),
     $container->get('logger.factory')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function snpRedirectToCancel(array &$form, FormStateInterface $form_state) {
    $parameters = $this->tempStore->get('parameters');
    $form_state->setRedirect('simple_node_importer.delete_node', $parameters);
  }

}
