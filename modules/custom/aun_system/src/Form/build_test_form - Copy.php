<?php
namespace Drupal\drupal_training\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Component\Utility\UrlHelper;
use Drupal\node\Entity\Node;


class build_test_form extends FormBase{
  function getFormId() {
    return 'build_form_test';
  }

  public function buildForm(array $form, FormStateInterface $form_state) {
    //$query =
    //print "dd";exit();

    $rows = [];
    $sql = "SELECT nid FROM node WHERE type ='student'";
    //print $sql;exit();
    $database = \Drupal::database();
    $result = $database->query($sql);
    $i =1;
    while ($row_data = $result->fetchAssoc()) {
      $row = array();
      $student_nid = $row_data['nid'];
      //print $student_nid;exit();
      $node = Node::load($student_nid);
      //print_r($node);exit();
      $row[] = $i;
      //$row[] = $student_nid;
      $row[] = $node->getTitle();
      $row[]= $node->field_student_number->value;
      //$row [] = $node->field_student_number['x-default'][0]['value'];
      $rows[] = $row;
      $i++;
    }

    $headerss = [
      ['data' => t('#')],
      ['data' => t('name')],
      ['data' => t('number')],
    ];

    $header = array(t("#"),t("name"),t("number"));
    $form['student_data'] = [
      '#type' => 'table',
      '#header' => $header,
      '#rows' => $rows,
    ];

    $form['title'] = array(
      '#type' => 'textfield',
      '#title' => t('Title'),
      '#required' => TRUE,
    );
    $form['video'] = array(
      '#type' => 'textfield',
      '#title' => t('Youtube video'),
    );
    $form['file_attachment'] = array(
      '#type' => 'file',
      '#title' => t('upload'),
    );

    $form['gender'] = array(
      '#type' => 'select',
      '#title' => t('Gender'),
      '#options'=>array("Male","Female"),
    );
    $form['video'] = array(
      '#type' => 'textfield',
      '#title' => t('Youtube video'),
    );
    $form['develop'] = array(
      '#type' => 'checkbox',
      '#title' => t('I would like to be involved in developing this material'),
    );
    $form['description'] = array(
      '#type' => 'textarea',
      '#title' => t('Description'),
    );
    $form['submit'] = array(
      '#type' => 'submit',
      '#value' => t('Submit'),
    );
    return $form;
  }

  public function validateForm(array &$form, FormStateInterface $form_state) {
    // Validate video URL.
    if (!UrlHelper::isValid($form_state->getValue('video'), TRUE)) {
      $form_state->setErrorByName('video', $this->t("The video url '%url' is invalid.", array('%url' => $form_state->getValue('video'))));
    }
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Display result.
    foreach ($form_state->getValues() as $key => $value) {
      drupal_set_message($key . ': ' . $value);
    }
  }
}
