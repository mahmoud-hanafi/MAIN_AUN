<?php  

/**  
 * @file  
 * Contains Drupal\newslettersignup\Form\NewsletterSignupAdminForm.  
 */  

namespace Drupal\newslettersignup\Form;  

use Drupal\Core\Form\ConfigFormBase;  
use Drupal\Core\Form\FormStateInterface;  

/**
 * Newsletter signup admin configuraiton form.
 */
class NewsletterSignupAdminForm extends ConfigFormBase {  
  /**  
   * {@inheritdoc}  
   */  
  protected function getEditableConfigNames() {  
    return [  
      'newslettersignup.adminsettings',  
    ];  
  }  

  /**  
   * {@inheritdoc}  
   */  
  public function getFormId() {  
    return 'newslettersignup_admin_form';  
  }  
  
  /**  
   * {@inheritdoc}  
   */  
  public function buildForm(array $form, FormStateInterface $form_state) {  
    $config = $this->config('newslettersignup.adminsettings');  

    $form['newslettersignup_admin_email'] = array(  
      '#type' => 'textfield',  
      '#title' => $this->t('Email address'),  
      '#description' => $this->t('Email address to which Newsletter Signup details to be sent'),  
      '#default_value' => $config->get('newslettersignup_admin_email'),  
      '#required' => TRUE,
    );
    $form['submit'] = array(
      '#type' => 'submit',
      '#value' => t('Save'),
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    /**
     * Validate email address
     */
    $newslettersignup_admin_email = trim($form_state->getValue('newslettersignup_admin_email'));
  
    if ($newslettersignup_admin_email !== '' && !\Drupal::service('email.validator')->isValid($newslettersignup_admin_email)) {
      $form_state->setErrorByName('newslettersignup_admin_email', $this->t('Invalid email address'));  
    }
  }

  /**  
   * {@inheritdoc}  
   */  
  public function submitForm(array &$form, FormStateInterface $form_state) {  
    $this->config('newslettersignup.adminsettings')  
      ->set('newslettersignup_admin_email', trim($form_state->getValue('newslettersignup_admin_email')))  
      ->save();  
  }    
}
