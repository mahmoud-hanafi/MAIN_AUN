<?php

/**
 * @file
 * Contains \Drupal\gavias_hook_themer\StringOverridesTranslation.
 */

namespace Drupal\gavias_hook_themer;

use Drupal\Core\StringTranslation\Translator\StaticTranslation;

/**
 * Provides string overrides.
 */
class StringOverridesTranslation extends StaticTranslation {

  /**
   * Constructs a StringOverridesTranslation object.
   */
  public function __construct() {
    parent::__construct();
  }

  /**
   * {@inheritdoc}
   */
  protected function getLanguage($langcode) {
    // This is just a dummy implementation.
    // @todo Replace this data.
    return array(
      '' => array(
        //'Product'                       => 'Course',
       // 'Add product'                   => 'Add Course',
        //'Products'                      => 'Courses',
        //'Product types'                 => 'Course types',
        // 'Product attributes'            => 'Course attributes',
        // 'Product variation types'       => 'Course variation types',
        // 'Manage your product attributes.' => 'Manage your course attributes.',
        // 'Add product variation type'      => 'Add course variation type',
        // 'Add product type'                => 'Add course type',
        // 'Add product attribute'           => 'Add course attribute',
        // 'Product variation type'          => 'Course variation type',
        // 'Product type'                    => 'Course type',
        // 'Product attribute'               => 'Course attribute'
      ),  
    );
  }

}
