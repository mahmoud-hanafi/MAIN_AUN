<?php

namespace Drupal\o365\Form;

use Drupal;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class ApiSettingsForm.
 */
class ApiSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'o365.api_settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'settings_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('o365.api_settings');
    $form['client_id'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Client ID'),
      '#description' => $this->t('The client ID as generated on your Microsoft portal'),
      '#maxlength' => 255,
      '#size' => 64,
      '#default_value' => $config->get('client_id'),
      '#required' => TRUE,
    ];
    $form['client_secret'] = [
      '#type' => 'password',
      '#title' => $this->t('Client secret'),
      '#description' => $this->t('The client secret as generated on your Microsoft portal.'),
      '#maxlength' => 255,
      '#size' => 64,
      '#default_value' => $config->get('client_secret'),
      '#required' => TRUE,
    ];
    $form['redirect_login'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Redirect after login URL'),
      '#description' => $this->t('After login users will be redirected to this page. This needs to be a full url like https://www.example.com.'),
      '#maxlength' => 255,
      '#size' => 64,
      '#default_value' => $config->get('redirect_login'),
      '#required' => TRUE,
    ];
    $form['redirect_callback'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Callback url'),
      '#description' => $this->t('This value should be: @url', ['@url' => 'https://' . Drupal::request()->getHost() . '/o365/callback']),
      '#maxlength' => 255,
      '#size' => 64,
      '#default_value' => empty($config->get('redirect_callback')) ? 'https://' . Drupal::request()->getHost() . '/o365/callback' : $config->get('redirect_callback'),
    ];
    $form['auth_scopes'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Authorization scopes'),
      '#description' => $this->t('A space separated list of authorization scopes. The offline_access scope (needed for login purposes) is automatically added.'),
      '#default_value' => $config->get('auth_scopes'),
      '#required' => TRUE,
    ];
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    $this->config('o365.api_settings')
      ->set('client_id', $form_state->getValue('client_id'))
      ->set('client_secret', $form_state->getValue('client_secret'))
      ->set('redirect_login', $form_state->getValue('redirect_login'))
      ->set('redirect_callback', $form_state->getValue('redirect_callback'))
      ->set('auth_scopes', $form_state->getValue('auth_scopes'))
      ->save();
  }

}
