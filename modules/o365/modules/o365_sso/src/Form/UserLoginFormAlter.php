<?php

namespace Drupal\o365_sso\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\user\Form\UserLoginForm;

/**
 * Provides a user login form.
 */
class UserLoginFormAlter extends UserLoginForm {

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);

    $url = Url::fromRoute('o365_sso.login_controller_login');
    $form['sso_login_link'] = [
      '#title' => $this->t('Login via SSO'),
      '#type'  => 'link',
      '#url'   => $url,
    ];

    return $form;
  }

}
