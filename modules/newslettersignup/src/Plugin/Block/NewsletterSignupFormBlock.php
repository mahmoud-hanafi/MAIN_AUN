<?php

namespace Drupal\newslettersignup\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides a 'Newsletter Signup form Block' block.
 *
 * @Block(
 *   id = "newslettersignup_block",
 *   admin_label = @Translation("Newsletter Signup Block"),
 *   category = @Translation("Newsletter Signup")
 * )
*/
class NewsletterSignupFormBlock extends BlockBase {

 /**
  * {@inheritdoc}
 */
 public function build() {
  /**
   * Include the already created newsletter signup form in a block 
   */
   $form = \Drupal::formBuilder()->getForm('Drupal\newslettersignup\Form\NewsletterSignupForm');
   $form['newsletteryourname']['#size'] = 20;
   $form['newslettercompanyname']['#size'] = 20;
   $form['newsletteryouremail']['#size'] = 20;
   
   return $form;
 }
 
}
