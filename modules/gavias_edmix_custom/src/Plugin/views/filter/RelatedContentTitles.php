<?php
/**
 * @file
 * Definition of Drupal\gavias_hook_themer\Plugin\views\filter\RelatedContentTitles.
 */
namespace Drupal\gavias_hook_themer\Plugin\views\filter;
use Drupal\views\Plugin\views\display\DisplayPluginBase;
use Drupal\views\Plugin\views\filter\ManyToOne;
use Drupal\views\ViewExecutable;
/**
 * Filters by given list of related content title options.
 *
 * @ingroup views_filter_handlers
 *
 * @ViewsFilter("gavias_hook_themer_related_content_titles")
 */
class RelatedContentTitles extends ManyToOne {
  /**
   * {@inheritdoc}
   */
  public function init(ViewExecutable $view, DisplayPluginBase $display, array &$options = NULL) {
    parent::init($view, $display, $options);
    $this->valueTitle = t('Allowed related courses titles');
    $this->definition['options callback'] = array($this, 'generateOptions');
  }
  
  /**
   * Helper function that generates the options.
   * @return array
   */
  public function generateOptions() {
    // $query = new EntityFieldQuery();
    // $query->entityCondition('entity_type', 'commerce_product')
    //    ->entityCondition('bundle', 'membership')
    //    ->propertyCondition('status', 1)
    //   ->fieldOrderBy('commerce_price', 'amount', 'ASC')
    //  ;
    // $result = $query->execute();

    $storage = \Drupal::entityManager()->getStorage('commerce_product');
    $relatedContentQuery = \Drupal::entityQuery('commerce_product')
      ->condition('type', array('default'))
      ->condition('status', 1);
    $relatedContentIds = $relatedContentQuery->execute(); 
    $res = array();
    foreach($relatedContentIds as $contentId){
      $res[$contentId] = $storage->load($contentId)->getTitle();
    }
    return $res;
  }
}