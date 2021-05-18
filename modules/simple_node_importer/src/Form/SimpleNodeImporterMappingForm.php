<?php

namespace Drupal\simple_node_importer\Form;

use Drupal\simple_node_importer\Services\GetServices;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\TempStore\PrivateTempStoreFactory;
use Drupal\Core\Session\SessionManagerInterface;
use Drupal\Core\Form\FormBase;
use Drupal\file\Entity\File;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\Core\Form\FormStateInterface;
use Drupal\node\NodeInterface;
use Drupal\file\FileUsage\FileUsageInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;

/**
 * Flexible Mapping Form for the Simple Node Importer.
 */
class SimpleNodeImporterMappingForm extends FormBase {

  protected $services;
  protected $sessionVariable;
  protected $sessionManager;
  protected $currentUser;
  protected $entityTypeManager;
  protected $fileUsage;
  protected $logger;

  /**
   * Confirmation form for the start node import.
   *
   * @param Drupal\simple_node_importer\Services\GetServices $getServices
   *   Constructs a Drupal\simple_node_importer\Services object.
   * @param Drupal\Core\TempStore\PrivateTempStoreFactory $sessionVariable
   *   Constructs a Drupal\Core\TempStore\PrivateTempStoreFactory object.
   * @param Drupal\Core\Session\SessionManagerInterface $session_manager
   *   Constructs a Drupal\Core\Session\SessionManagerInterface object.
   * @param Drupal\Core\Session\AccountInterface $current_user
   *   Constructs a Drupal\Core\Session\AccountInterface object.
   * @param Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Constructs a Drupal\Core\Entity\EntityTypeManagerInterface object.
   * @param Drupal\file\FileUsage\FileUsageInterface $file_usage
   *   Constructs a Drupal\file\FileUsage\FileUsageInterface object.
   * @param Drupal\Core\Logger\LoggerChannelFactoryInterface $logger
   *   Constructs a Drupal\Core\Logger\LoggerChannelFactoryInterface object.
   */
  public function __construct(GetServices $getServices, PrivateTempStoreFactory $sessionVariable, SessionManagerInterface $session_manager, AccountInterface $current_user, EntityTypeManagerInterface $entity_type_manager, FileUsageInterface $file_usage, LoggerChannelFactoryInterface $logger) {
    $this->services = $getServices;
    $this->sessionVariable = $sessionVariable->get('simple_node_importer');
    $this->sessionManager = $session_manager;
    $this->currentUser = $current_user;
    $this->entityTypeManager = $entity_type_manager;
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
   * Builds Flexible Mapping UI.
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
    elseif ($this->sessionVariable->get('file_upload_session') == FALSE) {
      $response = new RedirectResponse('/node/add/simple-node');
      $response->send();
    }
    else {
      // Options to be listed in File Column List.
      $headers = $this->services->simpleNodeImporterGetAllColumnHeaders($uri);
      $selected_content_type = $node->get('field_select_content_type')->getValue()[0]['value'];

      $entity_type = $node->getEntityTypeId();

      $type = 'mapping';

      $get_field_list = $this->services->snpGetFieldList($entity_type, $selected_content_type, $type);

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
      foreach ($get_field_list as $key => $field) {
        // code...
        if (method_exists($field->getLabel(), 'render')) {
          $form['mapping_form'][$key] = [
            '#type' => 'select',
            '#title' => $field->getLabel()->render(),
            '#options' => $headers,
            '#empty_option' => $this->t('Select'),
            '#empty_value' => '',
          ];
        }
        else {
          $field_name = $field->getName();
          $field_label = $field->getLabel();
          $field_info = FieldStorageConfig::loadByName('node', $field_name);

          if (!empty($field_info) && ($field_info->get('cardinality') == -1 || $field_info->get('cardinality') > 1)) {
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
      }

      // Get the preselected values for form fields.
      $form = $this->services->simpleNodeImporterGetPreSelectedValues($form, $headers);

      $form['snp_nid'] = [
        '#type' => 'hidden',
        '#value' => $node->id(),
      ];

      $form['import'] = [
        '#type' => 'submit',
        '#value' => $this->t('Import'),
        '#weight' => 49,
      ];

      $parameters = ['option' => $option, 'node' => $node->id()];
      $this->sessionVariable->set('parameters', $parameters);
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
   * Validates form.
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
   * Submit form.
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    // Remove unnecessary values.
    $form_state->cleanValues();

    $haystack = 'snp_';

    foreach ($form_state->getValues() as $key => $val) {
      if (strpos($key, $haystack) === FALSE) {
        $mapvalues[$key] = $val;
      }
    }

    $node_storage = $this->entityTypeManager->getStorage('node');

    $snp_nid = $form_state->getValue('snp_nid');

    $node = $node_storage->load($snp_nid);

    $bundleType = $node->get('field_select_content_type')->getValue()[0]['value'];

    $this->sessionVariable->set('mapvalues', $mapvalues);

    $parameters = ['type' => $bundleType, 'node' => $snp_nid];

    $form_state->setRedirect('simple_node_importer.confirm_importing', $parameters);
  }

  /**
   * {@inheritdoc}
   */
  public function snpRedirectToCancel(array &$form, FormStateInterface $form_state) {
    $parameters = $this->sessionVariable->get('parameters');
    $form_state->setRedirect('simple_node_importer.delete_node', $parameters);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
     $container->get('snp.get_services'),
     $container->get('user.private_tempstore'),
     $container->get('session_manager'),
     $container->get('current_user'),
     $container->get('entity_type.manager'),
     $container->get('file.usage'),
     $container->get('logger.factory')
    );
  }

}
