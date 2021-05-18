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
 * @ViewsField("drupal_slider_layers")
 */
class DrupalSliderViewsField extends FieldPluginBase {

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
    $options['slide_options'] = ['default' => ''];
    $options['background_img'] = ['default' => ''];
    $config = \Drupal::config('drupal_slider.settings');
    $layers_count = $config->get('ds_layers_count');
    for ($i = 1; $i <= $layers_count; $i++) {
      $options['layer_' . $i] = ['default' => ''];
    }

    return $options;
  }

  /**
   * Provide the options form.
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state) {
    $form['relationship']['#access'] = FALSE;
    $slide_options = [];
    $entity = \Drupal::entityTypeManager()->getStorage('drupal_slider_slide_grouping')->loadMultiple();
    foreach ($entity as $key => $value) {
      $slide_options[$key] = $value->label;
    }
    $form['slide_options'] = [
      '#type' => 'select',
      '#title' => $this->t('Slide options'),
      '#options' => $slide_options,
      '#default_value' => $this->options['slide_options'],
      '#description' => $this->t('Choose slides option set'),
      '#required' => TRUE,
    ];
    $form['background_img'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Background Image'),
      '#description' => $this->t("Give the background image URL token"),
      '#default_value' => $this->options['background_img'],
    ];
    $config = \Drupal::config('drupal_slider.settings');
    $layers_count = $config->get('ds_layers_count');
    for ($i = 1; $i <= $layers_count; $i++) {
      $form['layer_' . $i] = [
        '#type' => 'textarea',
        '#title' => 'Layer ' . $i,
        '#description' => $this->t('Use any text, image etc.'),
        '#default_value' => $this->options['layer_' . $i],
      ];
    }

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
      $layers = [];
      $slide_options = $this->options['slide_options'];
      $slide_grouping = \Drupal::entityTypeManager()->getStorage('drupal_slider_slide_grouping')->load($slide_options);
      $slides_list = $slide_grouping->get('slides');
      $view_result = $this->view->result;
      $views_count = count($view_result);
      $slides_list_count = count($slides_list);

      $slides_list_keys = array_keys($slides_list);
      for ($i = 0; $i < $views_count; $i++) {
        if ($slides_list_count >= ($i + 1)) {
          $slide_key = array_slice($slides_list_keys, ($i), 1);
          $layers_name[$view_result[$i]->nid] = $slides_list[$slide_key[0]]['slide_list'];
        }
      }
      if (isset($layers_name[$values->nid])) {
        $single_layer_name = $layers_name[$values->nid];
        $layer_load = \Drupal::entityTypeManager()->getStorage('drupal_slider_slide')->load($single_layer_name);
        $layers_attr = $layer_load->get('layers');
        if ($layers_attr) {
          $i = 1;
          foreach ($layers_attr as $key => $layer_attr) {
            $layers['layer_' . $i] = $layer_attr;
            $i++;
          }
        }
      }
      else {
        $layers_attr = [
          'attributes' =>
          [
            'horizontal' => '100',
            'vertical' => '100',
            'show_delay' => '0',
            'hide_delay' => '0',
            'show_transition' => 'left',
            'hide_transition' => 'left',
          ],
        ];
      }

      $background_img = $this->options['background_img'];

      $fields = $this->view->display_handler->getHandlers('field');
      $labels = $this->view->display_handler->getFieldLabels();
      foreach ($labels as $key => $var) {
        // If we find a replacement variable, replace it.
        if (strpos($background_img, "{{ $key }}") !== FALSE) {
          $field = $this->cleanVar($fields[$key]);
          $background_img = str_replace("{{ $key }}", $field, $background_img);
        }
      }
      $config = \Drupal::config('drupal_slider.settings');
      $layers_count = $config->get('ds_layers_count');
      for ($i = 1; $i <= $layers_count; $i++) {
        $no_replacements = TRUE;
        foreach ($labels as $key => $var) {
          $single_layer = $this->options['layer_' . $i];
          if (!empty(trim($single_layer))) {
            if (strpos($single_layer, "{{ $key }}") !== FALSE) {
              $field = $this->cleanVar($fields[$key]);
              if (isset($layers['layer_' . $i]['value'])) {
                $single_layer = $layers['layer_' . $i]['value'];
              }
              $layers['layer_' . $i]['value'] = str_replace("{{ $key }}", $field, $single_layer);
              $no_replacements = FALSE;
            }
          }
        }
        if ($no_replacements) {
          $layers['layer_' . $i]['value'] = $single_layer;
        }
      }

      return [
        '#theme' => 'drupal_slider_layers',
        '#background_img' => $background_img,
        '#layers_attributes' => $layers_attr,
        '#layers' => $layers,
      ];
    }
  }

}
