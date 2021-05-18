<?php

namespace Drupal\drupal_slider\Form;

use Drupal\Core\Form\FormStateInterface;

/**
 * Class SlideGroupAddForm.
 */
class SlideGroupAddForm extends SlideGroupBaseForm {

  /**
   * Function buildForm.
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    // Get anything we need from the base class.
    $form = parent::buildForm($form, $form_state);
    $entity = \Drupal::entityTypeManager()->getStorage('drupal_slider_slide')->loadMultiple();
    foreach ($entity as $key => $value) {
      $options[$key] = $key;
    }
    // Gather the number of names in the form already.
    $num_names = $form_state->get('num_names');
    // We have to ensure that there is at least one name field.
    if ($num_names === NULL) {
      $form_state->set('num_names', 1);
      $num_names = 1;
    }

    for ($i = 1; $i <= $num_names; $i++) {

      // TableDrag: Mark the table row as draggable.
      $form['slides'][$i]['#attributes']['class'][] = 'draggable';
      // TableDrag: Sort the table row according to its existing/configured
      // weight.
      $form['slides'][$i]['#weight'] = $i;

      // Some table columns containing raw markup.
      $form['slides'][$i]['name'] = [
        '#markup' => $i,
      ];
      $form['slides'][$i]['slide_list'] = [
        '#type' => 'select',
        '#title' => $this->t('Slide'),
        '#options' => $options,
        '#description' => $this->t('Choose the slide.'),
      ];

      // TableDrag: Weight column element.
      $form['slides'][$i]['weight'] = [
        '#type' => 'weight',
        '#title' => $this->t('Weight'),
        '#title_display' => 'invisible',
        '#default_value' => '',
        // Classify the weight element for #tabledrag.
        '#attributes' => ['class' => ['table-sort-weight']],
      ];
    }

    $form['actions']['add_name'] = [
      '#type' => 'submit',
      '#value' => $this->t('Add one more'),
      '#submit' => ['::addOne'],
      '#ajax' => [
        'callback' => '::addmoreCallback',
        'wrapper' => 'names-fieldset-wrapper',
      ],
    ];
    // If there is more than one name, add the remove button.
    if ($num_names >= 1) {
      $form['actions']['remove_name'] = [
        '#type' => 'submit',
        '#value' => $this->t('Remove one'),
        '#submit' => ['::removeCallback'],
        '#ajax' => [
          'callback' => '::addmoreCallback',
          'wrapper' => 'names-fieldset-wrapper',
        ],
      ];
    }
    return $form;
  }

  /**
   * Returns the actions provided by this form.
   *
   * For our add form, we only need to change the text of the submit button.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   An associative array containing the current state of the form.
   *
   * @return array
   *   An array of supported actions for the current entity form.
   */
  protected function actions(array $form, FormStateInterface $form_state) {
    $actions = parent::actions($form, $form_state);
    $actions['submit']['#value'] = $this->t('Create Group');
    return $actions;
  }

}
