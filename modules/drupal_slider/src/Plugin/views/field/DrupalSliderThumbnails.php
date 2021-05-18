<?php

namespace Drupal\drupal_slider\Plugin\views\field;

use Drupal\Core\Form\FormStateInterface;
use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\views\ResultRow;

/**
 * Field handler for Drupal Slider.
 *
 * @ingroup views_field_handlers
 *
 * @ViewsField("drupal_slider_thumbnails")
 */
class DrupalSliderThumbnails extends FieldPluginBase {

  /**
   * Define the query.
   *
   * @{inheritdoc}
   */
  public function query() {
    // Leave empty to avoid a query on this field.
  }

  /**
   * Define the available options.
   */
  protected function defineOptions() {
    $options = parent::defineOptions();
    $options['thumbnails'] = ['default' => ''];
    return $options;
  }

  /**
   * Provide the options form.
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state) {
    $form['relationship']['#access'] = FALSE;
    $form['thumbnails'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Thumbnails image url'),
      '#description' => $this->t("Give the Thumbnail image URL token"),
      '#default_value' => $this->options['thumbnails'],
    ];
    $form['replacements'] = [
      '#type' => 'fieldset',
      '#collapsible' => TRUE,
      '#collapsed' => TRUE,
      '#title' => $this->t('Replacement Variables'),
    ];
    $views_fields = $this->view->display_handler->getHandlers('field');
    foreach ($views_fields as $field => $handler) {
      if ($field == $this->options['id']) {
        break;
      }
      $items[] = "{{ $field }}";
    }
    $form['replacements']['variables'] = [
      '#theme' => 'item_list',
      '#items' => $items,
    ];
    parent::buildOptionsForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateOptionsForm(&$form, FormStateInterface $form_state) {

  }

  /**
   * Cleans a variable for handling later.
   */
  public function cleanVar($var) {
    $unparsed = isset($var->last_render) ? $var->last_render : '';
    return trim($unparsed);
  }

  /**
   * {@inheritdoc}
   */
  public function render(ResultRow $values) {
    if (!empty($values)) {
      $thumbnails = $this->options['thumbnails'];

      $fields = $this->view->display_handler->getHandlers('field');
      $labels = $this->view->display_handler->getFieldLabels();
      foreach ($labels as $key => $var) {
        // If we find a replacement variable, replace it.
        if (strpos($thumbnails, "{{ $key }}") !== FALSE) {
          $field = $this->cleanVar($fields[$key]);
          $thumbnails = str_replace("{{ $key }}", $field, $thumbnails);
        }
      }

      return $thumbnails;
    }
  }

}
