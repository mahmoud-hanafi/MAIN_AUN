<?php

/**
 * @file
 * Include file for defining views handlers and data.
 */

/**
 * Implements hook_views_data().
 */
function drupal_slider_views_data() {
  $data['drupal_slider']['table']['group'] = t('Global');
  $data['drupal_slider']['table']['join'] = [
    '#global' => [],
  ];

  $data['drupal_slider']['drupal_slider_layers'] = [
    'title' => t('Drupal Slider Layers'),
    'field' => [
      'title' => t('Drupal Slider Layers'),
      'help' => t('Shows views values in slider'),
      'id' => 'drupal_slider_layers',
    ],
  ];
  $data['drupal_slider']['drupal_slider_thumbnails'] = [
    'title' => t('Drupal Slider thumbnails'),
    'field' => [
      'title' => t('Drupal Slider thumbnails'),
      'help' => t('Shows thumbnails in slider'),
      'id' => 'drupal_slider_thumbnails',
    ],
  ];
  return $data;
}
