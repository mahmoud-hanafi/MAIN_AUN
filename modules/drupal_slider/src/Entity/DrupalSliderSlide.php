<?php

namespace Drupal\drupal_slider\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;

/**
 * Defines the drupal_slider_slide entity.
 *
 * @ingroup drupal_slider
 *
 * @ConfigEntityType(
 *   id = "drupal_slider_slide",
 *   label = @Translation("Drupal Slider - Slide"),
 *   admin_permission = "administer drupal slider",
 *   handlers = {
 *     "list_builder" = "Drupal\drupal_slider\Controller\SlideListBuilder",
 *     "form" = {
 *       "add" = "Drupal\drupal_slider\Form\SlideAddForm",
 *       "edit" = "Drupal\drupal_slider\Form\SlideEditForm",
 *       "delete" = "Drupal\drupal_slider\Form\SlideDeleteForm"
 *     }
 *   },
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "layers" = "layers",
 *     "uuid" = "uuid"
 *   },
 *   links = {
 *     "edit-form" = "/admin/config/media/drupal-slider/slide/{drupal_slider_slide}/edit",
 *     "delete-form" = "/admin/config/media/drupal-slider/slide/{drupal_slider_slide}/delete"
 *   },
 *   config_export = {
 *     "id",
 *     "uuid",
 *     "label",
 *     "layers"
 *   }
 * )
 */
class DrupalSliderSlide extends ConfigEntityBase {
  /**
   * {@inheritdoc}
   */
  public $id;
  /**
   * {@inheritdoc}
   */
  public $uuid;
  /**
   * {@inheritdoc}
   */
  public $label;
  /**
   * {@inheritdoc}
   */
  public $layers;

}
