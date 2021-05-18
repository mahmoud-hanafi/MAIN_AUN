<?php

namespace Drupal\drupal_slider\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;

/**
 * Defines the drupal_slider_slide_grouping entity.
 *
 * @ingroup drupal_slider
 *
 * @ConfigEntityType(
 *   id = "drupal_slider_slide_grouping",
 *   label = @Translation("Drupal Slider - Slide Grouping"),
 *   admin_permission = "administer drupal slider",
 *   handlers = {
 *     "list_builder" = "Drupal\drupal_slider\Controller\SlideGroupListBuilder",
 *     "form" = {
 *       "add" = "Drupal\drupal_slider\Form\SlideGroupAddForm",
 *       "edit" = "Drupal\drupal_slider\Form\SlideGroupEditForm",
 *       "delete" = "Drupal\drupal_slider\Form\SlideGroupDeleteForm"
 *     }
 *   },
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "slides" = "slides",
 *     "uuid" = "uuid"
 *   },
 *   links = {
 *     "edit-form" = "/admin/config/media/drupal-slider/slide-group/{drupal_slider_slide_grouping}/edit",
 *     "delete-form" = "/admin/config/media/drupal-slider/slide-group/{drupal_slider_slide_grouping}/delete"
 *   },
 *   config_export = {
 *     "id",
 *     "uuid",
 *     "label",
 *     "slides"
 *   }
 * )
 */
class DrupalSliderSlideGrouping extends ConfigEntityBase {
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
  public $slides;

}
