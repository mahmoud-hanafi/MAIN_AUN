<?php

namespace Drupal\drupal_slider\Plugin\views\style;

use Drupal\Core\Form\FormStateInterface;
use Drupal\views\Plugin\views\style\StylePluginBase;

/**
 * Views_style_plugins for drupal_slider.
 *
 * @ViewsStyle(
 *   id = "drupal_slider",
 *   title = @Translation("Drupal Slider"),
 *   help = @Translation("Displays a view as a Slider, using the Drupal Slider + Slider Pro jQuery plugin."),
 *   theme = "drupal_slider_views_style",
 *   theme_file = "drupal_slider_views.theme.inc",
 *   display_types = {"normal"}
 * )
 */
class DrupalSlider extends StylePluginBase {
  /**
   * {@inheritdoc}
   */
  protected $usesRowPlugin = TRUE;
  /**
   * {@inheritdoc}
   */
  protected $usesRowClass = FALSE;
  /**
   * {@inheritdoc}
   */
  protected $usesGrouping = FALSE;
  /**
   * {@inheritdoc}
   */
  protected $usesFields = TRUE;

  /**
   * Function defineOptions.
   */
  protected function defineOptions() {
    $options = parent::defineOptions();
    $options['general'] = ['contains' => []];
    $options['general']['contains']['width'] = ['default' => 950];
    $options['general']['contains']['height'] = ['default' => 300];
    $options['general']['contains']['arrows'] = ['default' => 1];
    $options['general']['contains']['buttons'] = ['default' => 1];
    $options['general']['contains']['autoplay'] = ['default' => 1];
    $options['general']['contains']['shuffle'] = ['default' => 0];
    $options['general']['contains']['fade'] = ['default' => 0];
    $options['general']['contains']['carousel'] = ['default' => 0];
    $options['general']['contains']['full_screen'] = ['default' => 0];
    $options['general']['contains']['loop'] = ['default' => 1];
    $options['general']['contains']['orientation'] = ['default' => 0];
    $options['general']['contains']['thumbnails_position'] = ['default' => 'bottom'];
    return $options;
  }

  /**
   * Function buildOptionsForm.
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state) {
    parent::buildOptionsForm($form, $form_state);
    $fields = $this->getAvailableFields();
    if (empty($fields)) {
      drupal_set_message($this->t('To configure Drupal Slider you have to add at least one field'), 'error');
      return $form;
    }

    $form['general'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('General Settings'),
      '#open' => TRUE,
    ];
    $form['general']['width'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Width'),
      '#description' => $this->t('The width of the slider. Eg 200px or 50%.'),
      '#default_value' => $this->options['general']['width'],
    ];
    $form['general']['height'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Height'),
      '#default_value' => $this->options['general']['height'],
      '#description' => $this->t('The height of the slider. Eg 200px. Dont use %.'),
    ];
    $form['general']['arrows'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Arrows'),
      '#default_value' => $this->options['general']['arrows'],
      '#description' => $this->t('Navigation arrows on slides.'),
    ];
    $form['general']['buttons'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Buttons'),
      '#default_value' => $this->options['general']['buttons'],
      '#description' => $this->t('Shows current slide position'),
    ];
    $form['general']['autoplay'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Autoplay'),
      '#default_value' => $this->options['general']['autoplay'],
      '#description' => $this->t('Autoplay the slides.'),
    ];
    $form['general']['shuffle'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Shuffle the slides'),
      '#default_value' => $this->options['general']['shuffle'],
      '#description' => $this->t('Shuffles the slides.'),
    ];
    $form['general']['fade'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Fade effect'),
      '#default_value' => $this->options['general']['fade'],
      '#description' => $this->t('Fade effect on transition.'),
    ];
    $form['general']['carousel'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Show carousel instead of slider'),
      '#default_value' => $this->options['general']['carousel'],
      '#description' => $this->t('By checking this it will turn into carousel. The image width should be less than window size.'),
    ];
    $form['general']['full_screen'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Full screen'),
      '#default_value' => $this->options['general']['full_screen'],
      '#description' => $this->t('Add full screen button to the slider'),
    ];
    $form['general']['loop'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Loops slides'),
      '#default_value' => $this->options['general']['full_screen'],
      '#description' => $this->t('slider will repeat the slides.'),
    ];
    $form['general']['orientation'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Vertical orientation'),
      '#default_value' => $this->options['general']['orientation'],
      '#description' => $this->t('Indicates whether the slides will be arranged horizontally or vertically.'),
    ];
    $form['general']['thumbnails_position'] = [
      '#type' => 'select',
      '#title' => $this->t('Thumbnails Position'),
      '#options' => [
        'bottom' => 'Bottom',
        'top' => 'Top',
        'left' => 'Left',
        'right' => 'Right',
      ],
      '#default_value' => $this->options['general']['thumbnails_position'],
    ];
  }

  /**
   * Returns option list of fields available on view.
   */
  protected function getAvailableFields() {
    $view = $this->view;
    return $view->display_handler->getFieldLabels();
  }

}
