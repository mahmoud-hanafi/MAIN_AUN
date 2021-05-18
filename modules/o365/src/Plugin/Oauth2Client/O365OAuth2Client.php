<?php

namespace Drupal\o365\Plugin\Oauth2Client;

use Drupal;
use Drupal\oauth2_client\Plugin\Oauth2Client\Oauth2ClientPluginBase;

/**
 * OAuth2 Client to authenticate with Office 365.
 *
 * @Oauth2Client(
 *   id = "o365",
 *   name = @Translation("Office 365"),
 *   grant_type = "client_credentials",
 *   client_id = "",
 *   client_secret = "",
 *   authorization_uri = "https://login.microsoftonline.com/common/oauth2/v2.0/authorize",
 *   token_uri = "https://login.microsoftonline.com/common/oauth2/v2.0/token",
 *   resource_owner_uri = "",
 * )
 */
class O365OAuth2Client extends Oauth2ClientPluginBase {

  /**
   * {@inheritdoc}
   */
  public function getClientId() {
    $config = Drupal::config('o365.api_settings');
    return $config->get('client_id');
  }

  /**
   * {@inheritdoc}
   */
  public function getClientSecret() {
    $config = Drupal::config('o365.api_settings');
    return $config->get('tenant_id');
  }

}
