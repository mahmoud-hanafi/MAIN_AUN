<?php

namespace Drupal\drupal_slider\Controller;

use Drupal\Core\Config\Entity\ConfigEntityListBuilder;
use Drupal\Core\Entity\EntityInterface;

/**
 * Provides a listing of drupal_slider entities.
 *
 * @ingroup drupal_slider
 */
class SlideGroupListBuilder extends ConfigEntityListBuilder {

  /**
   * {@inheritdoc}
   */
  protected function getModuleName() {
    return 'drupal_slider';
  }

  /**
   * Builds the header row for the entity listing.
   *
   * @return array
   *   A render array structure of header strings.
   *
   * @see \Drupal\Core\Entity\EntityListController::render()
   */
  public function buildHeader() {
    $header['label'] = $this->t('Group name');
    return $header + parent::buildHeader();
  }

  /**
   * Function buildRow.
   */
  public function buildRow(EntityInterface $entity) {
    $row['label'] = $entity->label();
    return $row + parent::buildRow($entity);
  }

  /**
   * Function render.
   */
  public function render() {
    $build[] = parent::render();
    return $build;
  }

}
