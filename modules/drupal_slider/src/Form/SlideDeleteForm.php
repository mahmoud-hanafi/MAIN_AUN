<?php

namespace Drupal\drupal_slider\Form;

use Drupal\Core\Entity\EntityConfirmFormBase;
use Drupal\Core\Url;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class SlideDeleteForm.
 *
 * @ingroup drupal_slider
 */
class SlideDeleteForm extends EntityConfirmFormBase {

  /**
   * Function getQuestion.
   */
  public function getQuestion() {
    return $this->t('Are you sure you want to delete slide %label?', [
      '%label' => $this->entity->label(),
    ]);
  }

  /**
   * Function getConfirmText.
   */
  public function getConfirmText() {
    return $this->t('Delete Slide');
  }

  /**
   * Function getCancelUrl.
   */
  public function getCancelUrl() {
    return new Url('entity.drupal_slider_slide.list');
  }

  /**
   * Function submitForm.
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Delete the entity.
    $this->entity->delete();

    // Set a message that the entity was deleted.
    $this->messenger()->addMessage($this->t('Slide %label was deleted.', [
      '%label' => $this->entity->label(),
    ]));

    // Redirect the user to the list controller when complete.
    $form_state->setRedirectUrl($this->getCancelUrl());
  }

}
