<?php

namespace Drupal\drupal_slider\Form;

use Drupal\Core\Form\FormStateInterface;

/**
 * Class SlideGroupEditForm.
 */
class SlideGroupEditForm extends SlideGroupBaseForm {

  /**
   * Function buildForm.
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $form = parent::buildForm($form, $form_state);
    $slideGroup = $this->entity;
    $entity = \Drupal::entityTypeManager()->getStorage('drupal_slider_slide')->loadMultiple();
    foreach ($entity as $key => $value) {
      $options[$key] = $key;
    }

    $slides = $slideGroup->get('slides');
    $slides_count = count($slides);

    $num_names = $form_state->get('num_names');
    if ($num_names === NULL) {
      $num_names = $slides_count;
      $form_state->set('num_names', $num_names);
    }

    for ($i = 1; $i <= $num_names; $i++) {

      $single_slide = array_slice($slides, ($i - 1), 1);

      $form['slides'][$i]['#attributes']['class'][] = 'draggable';

      $form['slides'][$i]['#weight'] = isset($single_slide[0]['weight']) ? $single_slide[0]['weight'] : $i;

      $form['slides'][$i]['name'] = [
        '#markup' => $i,
      ];
      $form['slides'][$i]['slide_list'] = [
        '#type' => 'select',
        '#title' => $this->t('Slide'),
        '#options' => $options,
        '#default_value' => isset($single_slide[0]['slide_list']) ? $single_slide[0]['slide_list'] : '',
        '#description' => $this->t('Choose the slide.'),
      ];
      // TableDrag: Weight column element.
      $form['slides'][$i]['weight'] = [
        '#type' => 'weight',
        '#title' => $this->t('Weight'),
        '#title_display' => 'invisible',
        '#default_value' => isset($single_slide[0]['weight']) ? $single_slide[0]['weight'] : $i,
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
    if ($num_names > 1) {
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
   * For the edit form, we only need to change the text of the submit button.
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
    $actions['submit']['#value'] = $this->t('Update Group');
    return $actions;
  }

}
