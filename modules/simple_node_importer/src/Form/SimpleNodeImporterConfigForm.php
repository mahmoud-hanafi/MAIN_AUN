<?php

namespace Drupal\simple_node_importer\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Configuration Form for the Simple Node Importer.
 */
class SimpleNodeImporterConfigForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'simple_node_importer_config_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['simple_node_importer.settings'];
  }

  protected $contentTypes;

  /**
   * Constructs variables.
   *
   * @var array $contentTypes
   *   The information from the GetContentTypes service for this form.
   * @var bool $checkAvailablity
   *   To check the availability of Content Type exists or not.
   */
  public function __construct($contentTypes, $checkAvailablity) {
    $this->contentTypes = $contentTypes;
    $this->checkAvailablity = $checkAvailablity;
  }

  /**
   * Builds config form.
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $config = $this->config('simple_node_importer.settings');

    $content_type_selected = [];

    $content_type_select = $config->get('content_type_select');
    $entity_type_options = [
      'node' => 'node',
      'user' => 'user',
      'taxonomy' => 'taxonomy',
    ];

    if (!empty($content_type_select)) {
      foreach ($content_type_select as $key => $value) {
        if ($value) {
          $content_type_selected[$key] = $value;
        }
      }
    }
    $form['fieldset_entity_type'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('entity type settings'),
      '#weight' => 1,
      '#collapsible' => TRUE,
      '#collapsed' => FALSE,
    ];
    $form['fieldset_entity_type']['entity_type_select'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Select entity type'),
      '#default_value' => $config->get('entity_type_select'),
      '#options' => $entity_type_options,
      '#description' => $this->t('Configuration for the entity type to be selected.'),
      '#required' => FALSE,
    ];

    $form['fieldset_content_type'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Content type settings'),
      '#weight' => 1,
      '#collapsible' => TRUE,
      '#collapsed' => FALSE,
      '#states' => [
        'visible' => [
          ':input[name="entity_type_select[node]"]' => [
              ['checked' => TRUE],
          ],
        ],
      ],
    ];

    $form['fieldset_content_type']['content_type_select'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Select content type'),
      '#default_value' => isset($content_type_selected) ? $content_type_selected : NULL,
      '#options' => $this->contentTypes,
      '#description' => $this->t('Configuration for the content type to be selected.'),
      '#required' => FALSE,
    ];

    $form['fieldset_user_auto_create_settings'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('User Auto Creation Settings'),
      '#weight' => 1,
      '#collapsible' => TRUE,
      '#collapsed' => FALSE,
    ];

    // The options to display in our form radio buttons.
    $options = [
      'admin' => $this->t('Set Admin as default author.'),
      'current' => $this->t('Set current user as default author.'),
      'new' => $this->t('Create new user with authenticated role.'),
    ];

    $form['fieldset_user_auto_create_settings']['simple_node_importer_allow_user_autocreate'] = [
      '#type' => 'radios',
      '#options' => $options,
      '#default_value' => $config->get('simple_node_importer_allow_user_autocreate'),
      '#description' => $this->t('User will be set accordingly, if the provided value for author in csv is not avaiable in the system.'),
    ];

    $form['fieldset_taxonomy_term_type'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Taxonomy Term settings'),
      '#weight' => 1,
      '#collapsible' => TRUE,
      '#collapsed' => FALSE,
    ];

    $form['fieldset_taxonomy_term_type']['simple_node_importer_allow_add_term'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Allow adding new taxonomy terms.'),
      '#default_value' => $config->get('simple_node_importer_allow_add_term'),
      '#description' => $this->t('Check to allow adding term for taxonomy reference fields, if term is not available.'),
    ];

    $form['fieldset_remove_importer'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Node remove settings'),
      '#weight' => 2,
      '#collapsible' => TRUE,
      '#collapsed' => FALSE,
    ];

    // The options to display in our checkboxes.
    $option = [
      'option' => $this->t('Delete import logs.'),
    ];

    $form['fieldset_remove_importer']['node_delete'] = [
      '#title' => '',
      '#type' => 'checkboxes',
      '#description' => $this->t('Select the checkbox to delete all import logs permanently.'),
      '#options' => $option,
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('simple_node_importer.settings');

    $config->set('entity_type_select', $form_state->getValue('entity_type_select'))
      ->set('content_type_select', $form_state->getValue('content_type_select'))
      ->set('simple_node_importer_allow_user_autocreate', $form_state->getValue('simple_node_importer_allow_user_autocreate'))
      ->set('simple_node_importer_allow_add_term', $form_state->getValue('simple_node_importer_allow_add_term'))
      ->set('node_delete', $form_state->getValue('node_delete'))->save();

    if (method_exists($this, 'submitFormDeleteLogs')) {
      $this->submitFormDeleteLogs($form, $form_state);
    }

    parent::submitForm($form, $form_state);
  }

  /**
   * Delete import logs.
   */
  public function submitFormDeleteLogs(array &$form, FormStateInterface $form_state) {

    if ($this->checkAvailablity) {

      $node_setting = $form_state->getValue(['node_delete', 'deletelog']);
      $bundle = 'simple_node';
      $query = \Drupal::entityQuery('node');
      $query->condition('status', 1);
      $query->condition('type', $bundle);
      $nids = $query->execute();

      if ($node_setting === 'deletelog' && !empty($nids)) {
        entity_delete_multiple('node', $nids);
        drupal_set_message($this->t('%count nodes has been deleted.', ['%count' => count($nids)]));
      }
      elseif ($node_setting === 'deletelog' && empty($nids)) {
        drupal_set_message($this->t("Oops there is nothing to delete"));
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('snp.get_services')->getContentTypeList(),
      $container->get('snp.get_services')->checkAvailablity()
    );
  }

}
