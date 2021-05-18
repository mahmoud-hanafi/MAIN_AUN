<?php

/**
 * @file
 * Alters theming & template .
 */

/**
 * Prepares variables for Drupal slider view templates.
 *
 * Default template: slider-pro-views-style.html.twig.
 */
function template_preprocess_drupal_slider_views_style(array &$variables) {
  if (empty($variables['view'])) {
    return;
  }
  // View options set by user.
  $options = $variables['view']->style_plugin->options;
  $id = 'drupal-slider-' . uniqid();
  $drupalSlider['id'] = $id;
  $drupalSlider['width'] = isset($options['general']['width']) && $options['general']['width'] ? $options['general']['width'] : '100%';
  $drupalSlider['height'] = isset($options['general']['height']) && $options['general']['height'] ? $options['general']['height'] : '300';
  $drupalSlider['arrows'] = isset($options['general']['arrows']) && $options['general']['arrows'] ? $options['general']['arrows'] : 0;
  $drupalSlider['buttons'] = isset($options['general']['buttons']) && $options['general']['buttons'] ? $options['general']['buttons'] : 0;
  $drupalSlider['autoplay'] = isset($options['general']['autoplay']) && $options['general']['autoplay'] ? $options['general']['autoplay'] : 0;
  $drupalSlider['fade'] = isset($options['general']['fade']) && $options['general']['fade'] ? $options['general']['fade'] : 0;
  $drupalSlider['shuffle'] = isset($options['general']['shuffle']) && $options['general']['shuffle'] ? $options['general']['shuffle'] : 0;
  $drupalSlider['full_screen'] = isset($options['general']['full_screen']) && $options['general']['full_screen'] ? $options['general']['full_screen'] : 0;
  $drupalSlider['carousel'] = isset($options['general']['carousel']) && $options['general']['carousel'] ? 1 : 0;
  $drupalSlider['loop'] = isset($options['general']['loop']) && $options['general']['loop'] ? $options['general']['loop'] : 0;
  $drupalSlider['orientation'] = isset($options['general']['orientation']) && $options['general']['orientation'] ? $options['general']['orientation'] : 0;
  $drupalSlider['thumbnails_position'] = isset($options['general']['thumbnails_position']) && $options['general']['thumbnails_position'] ? $options['general']['thumbnails_position'] : 'bottom';

  $content['#attached'] = [
    'drupalSettings' => [
      'drupalSlider' => $drupalSlider,
    ],
    'library' => [
      'drupal_slider/slider.pro',
      'drupal_slider/drupal_slider.load',
    ],
  ];
  // Add the slide items to the variables.
  $variables['attached'] = $content;
  $variables['id'] = $id;
}
