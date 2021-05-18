<?php

namespace Drupal\simple_node_importer\Form;

use Drupal\simple_node_importer\Services\GetServices;
use Drupal\Component\Render\FormattableMarkup;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\TempStore\PrivateTempStoreFactory;
use Drupal\Core\Session\SessionManagerInterface;
use Drupal\Core\Form\ConfirmFormBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\node\NodeInterface;
use Drupal\Core\Url;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Defines a confirmation form to confirm deletion of something by id.
 */
class SimpleNodeConfirmImportForm extends ConfirmFormBase {

  protected $services;
  protected $sessionVariable;
  protected $sessionManager;
  protected $currentUser;
  protected $entityTypeManager;

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
   */
  public function __construct(GetServices $getServices, PrivateTempStoreFactory $sessionVariable, SessionManagerInterface $session_manager, AccountInterface $current_user, EntityTypeManagerInterface $entity_type_manager) {
    $this->services = $getServices;
    $this->sessionVariable = $sessionVariable->get('simple_node_importer');
    $this->sessionManager = $session_manager;
    $this->currentUser = $current_user;
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, string $type = NULL, NodeInterface $node = NULL) {
    $this->node = $node;
    $form['snp_nid'] = [
      '#type' => 'hidden',
      '#value' => $node->id(),
    ];
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Remove unnecessary values.
    $form_state->cleanValues();

    $node_storage = $this->entityTypeManager->getStorage('node');
    $file_storage = $this->entityTypeManager->getStorage('file');

    $snp_nid = $form_state->getValue('snp_nid');

    $node = $node_storage->load($snp_nid);

    $bundleType = $node->get('field_select_content_type')->getValue()[0]['value'];

    $map_values = $this->sessionVariable->get('mapvalues');
    $fid = $node->get('field_upload_csv')->getValue()[0]['target_id'];
    $file = $file_storage->load($fid);
    $csv_uri = $file->getFileUri();
    $handle = fopen($csv_uri, 'r');
    $columns = [];
    $columns = array_values($this->services->simpleNodeImporterGetAllColumnHeaders($csv_uri));
    $record = [];
    $map_fields = array_keys($map_values);
    $i = 1;
    while ($row = fgetcsv($handle)) {
      if ($i == 1) {
        $i++;
        continue;
      }

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
      $record['nid'] = $node->id();
      $record['type'] = $bundleType;

      $records[] = $record;
    }

    // Preapring batch parmeters to be execute.
    $batch = [
      'title' => t('Importing content to :bundleType.', [':bundleType' => $bundleType]),
      'operations' => [
              [
                '\Drupal\simple_node_importer\Controller\NodeImportController::simpleNodeCreate',
                [$records],
              ],
      ],
      'finished' => '\Drupal\simple_node_importer\Controller\NodeImportController::nodeImportBatchFinished',
      'init_message' => t('Initialializing content importing.'),
      'progress_message' => t('Processed @current out of @total.'),
      'error_message' => t('Node creation has encountered an error.'),
    ];

    // Set the batch operation.
    batch_set($batch);
    fclose($handle);
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() : string {
    return "simple_node_confirm_importing_form";
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    $bundleType = $this->node->get('field_select_content_type')->getValue()[0]['value'];
    $nid = $this->node->id();
    $parameters = ['option' => $bundleType, 'node' => $nid];
    return new Url('simple_node_importer.node_mapping_form', $parameters);
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {

    $critical_info = new FormattableMarkup('<p class="@class">If email id\'s provided in the "Authored By" column of your CSV match the existing users in the system, then data will be automatically imported. If not, the users will have to be created before importing the data.</p><p>Do you want to continue?</p>', ["@class" => "confirmation-info"]);

    return $this->t("@critical_info", ["@critical_info" => $critical_info]);
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
             $container->get('entity_type.manager')
    );
  }

}
