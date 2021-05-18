<?php

namespace Drupal\drupal_slider\Form;

use Drupal\Core\Form\FormStateInterface;

/**
 * Class SlideEditForm.
 *
 * Provides the edit form for our drupal_slider_slide entity.
 */
class SlideEditForm extends SlideBaseForm {

  /**
   * Function buildForm.
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $form = parent::buildForm($form, $form_state);
    $slide = $this->entity;

    $layers = $slide->get('layers');
    $layers_count = count($layers);

    $num_names = $form_state->get('num_names');
    if ($num_names === NULL) {
      $num_names = $layers_count;
      $form_state->set('num_names', $num_names);
    }

    for ($i = 1; $i <= $num_names; $i++) {
      $single_layer = array_slice($layers, ($i - 1), 1);

      $form['layers'][$i]['#attributes']['class'][] = 'draggable';
      // TableDrag: Sort the table row according to its existing/configured
      // weight.
      $form['layers'][$i]['#weight'] = isset($single_layer[0]['weight']) ? $single_layer[0]['weight'] : $i;

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
        '#default_value' => isset($single_layer[0]['attributes']['horizontal']) ? $single_layer[0]['attributes']['horizontal'] : '',
      ];
      $form['layers'][$i]['attributes']['vertical'] = [
        '#title' => 'Vertical',
        '#type' => 'textfield',
        '#required' => TRUE,
        '#default_value' => isset($single_layer[0]['attributes']['vertical']) ? $single_layer[0]['attributes']['vertical'] : '',
      ];
      $form['layers'][$i]['attributes']['show_delay'] = [
        '#title' => 'Show delay',
        '#type' => 'number',
        '#required' => TRUE,
        '#default_value' => isset($single_layer[0]['attributes']['show_delay']) ? $single_layer[0]['attributes']['show_delay'] : 0,
      ];
      $form['layers'][$i]['attributes']['hide_delay'] = [
        '#title' => 'Hide delay',
        '#type' => 'number',
        '#required' => TRUE,
        '#default_value' => isset($single_layer[0]['attributes']['hide_delay']) ? $single_layer[0]['attributes']['hide_delay'] : 0,
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
        '#default_value' => $single_layer[0]['attributes']['show_transition'],
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
        '#default_value' => $single_layer[0]['attributes']['hide_transition'],
      ];
      // TableDrag: Weight column element.
      $form['layers'][$i]['weight'] = [
        '#type' => 'weight',
        '#title' => $this->t('Weight'),
        '#title_display' => 'invisible',
        '#default_value' => isset($single_layer[0]['weight']) ? $single_layer[0]['weight'] : '',
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
    $actions['submit']['#value'] = $this->t('Update Slide');
    return $actions;
  }

}
