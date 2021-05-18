<?php
namespace Drupal\aun_system\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Component\Utility\UrlHelper;
use Drupal\node\Entity\Node;
use Drupal\file\Entity\File;


class build_results_import_form extends FormBase{
  function getFormId() {
    return 'build_results_import_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state) {
    //$query =
    //print "dd";exit();

   $form['description'] = array(
      '#markup' => '<p>Use this form to upload a CSV file of Data</p>',
    );

    $form['import_csv'] = array(
      '#type' => 'managed_file',
      '#title' => t('Upload file here'),
      '#upload_location' => 'public://importcsv/',
      '#default_value' => '',
      "#upload_validators"  => array("file_validate_extensions" => array("csv")),
      '#states' => array(
        'visible' => array(
          ':input[name="File_type"]' => array('value' => t('Upload Your File')),
        ),
      ),
    );

    $form['actions']['#type'] = 'actions';


    $form['actions']['submit'] = array(
      '#type' => 'submit',
      '#value' => $this->t('Upload CSV'),
      '#button_type' => 'primary',
    );
    return $form;

  }

  public function validateForm(array &$form, FormStateInterface $form_state) {
    // Validate video URL.
    if (!UrlHelper::isValid($form_state->getValue('video'), TRUE)) {
//      $form_state->setErrorByName('video', $this->t("The video url '%url' is invalid.", array('%url' => $form_state->getValue('video'))));
    }
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Display result.
	$csv_file = $form_state->getValue('import_csv');

    /* Load the object of the file by it's fid */
    $file = File::load( $csv_file[0] );

    /* Set the status flag permanent of the file object */
    $file->setPermanent();

    /* Save the file in database */
    $file->save();
	
	/*
	$file = $form_state['values']['csv_upload'];
    $file->status = FILE_STATUS_PERMANENT;
    $file->filename = str_replace(' ', '_', $file->filename);
    file_save($file);
	*/
    $csv_file = file_load($file->fid);
	print_r($csv_file);exit();
    $file = fopen($csv_file->uri, "r");
    while(! feof($file))      {
      $employees = fgetcsv($file);
	  print count($employees);
	  
	}
	exit();
//print "dd";exit();
    // You can use any sort of function to process your data. The goal is to get each 'row' of data into an array
    // If you need to work on how data is extracted, process it here.
    $data = $this->csvtoarray($file->getFileUri(), ',');
    foreach($data as $row) {
      $operations[] = ['\Drupal\IMPORT_EXAMPLE\addImportContent::addImportContentItem', [$row]];
	  print $row." - ";
    }
	exit();
    foreach ($form_state->getValues() as $key => $value) {
      //drupal_set_message($key . ': ' . $value);
    }
  }
}
