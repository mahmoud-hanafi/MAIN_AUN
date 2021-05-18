<?php

namespace Drupal\drupal_slider\Form;

use Drupal\Core\Form\FormStateInterface;

/**
 * Class SlideAddForm.
 *
 * Provides the add form for our drupal_slider_slide entity.
 *
 * @ingroup drupal_slider
 */
class SlideAddForm extends SlideBaseForm {

  /**
   * Function buildForm.
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    // Get anything we need from the base class.
    $form = parent::buildForm($form, $form_state);

    $num_names = $form_state->get('num_names');
    if ($num_names === NULL) {
      $form_state->set('num_names', 1);
      $num_names = 1;
    }

    for ($i = 1; $i <= $num_names; $i++) {
      // TableDrag: Mark the table row as draggable.
      $form['layers'][$i]['#attributes']['class'][] = 'draggable';
      // TableDrag: Sort the table row according to its existing/configured
      // weight.
      $form['layers'][$i]['#weight'] = $i;

      // Some table columns containing raw markup.
      $form['layers'][$i]['name'] = [
        '#markup' => $i,
      ];
      $form['layers'][$i]['attributes'] = [
        '#type' => 'fieldset',
        '#title' => '',
        '#open' => TRUE,
      ];
      $form['layers'][$i]['attributes']['horizontal'] = [
        '#title' => 'Horizontal',
        '#type' => 'textfield',
        '#required' => TRUE,
        '#default_value' => '',
      ];
      $form['layers'][$i]['attributes']['vertical'] = [
        '#title' => 'Vertical',
        '#type' => 'textfield',
        '#required' => TRUE,
        '#default_value' => '',
      ];
      $form['layers'][$i]['attributes']['show_delay'] = [
        '#title' => 'Show delay',
        '#type' => 'number',
        '#required' => TRUE,
        '#default_value' => 0,
      ];
      $form['layers'][$i]['attributes']['hide_delay'] = [
        '#title' => 'Hide delay',
        '#type' => 'number',
        '#required' => TRUE,
        '#default_value' => 0,
      ];
      $form['layers'][$i]['attributes']['show_transition'] = [
        '#type' => 'select',
        '#title' => $this->t('Show transition'),
        '#options' => [
          'left' => $this->t('Left'),
          'right' => $this->t('Right'),
          'top' => $this->t('Top'),
          'bottom' => $this->t('Bottom'),
          'up' => $this->t('Up'),
          'down' => $this->t('Down'),
        ],
      ];
      $form['layers'][$i]['attributes']['hide_transition'] = [
        '#type' => 'select',
        '#title' => $this->t('Hide transition'),
        '#options' => [
          'left' => $this->t('Left'),
          'right' => $this->t('Right'),
          'top' => $this->t('Top'),
          'bottom' => $this->t('Bottom'),
          'up' => $this->t('Up'),
          'down' => $this->t('Down'),
        ],
      ];
      // TableDrag: Weight column element.
      $form['layers'][$i]['weight'] = [
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
    $actions['submit']['#value'] = $this->t('Create Slide');
    return $actions;
  }

}
