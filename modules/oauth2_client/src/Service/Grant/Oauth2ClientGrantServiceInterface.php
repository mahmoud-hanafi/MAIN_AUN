<?php

namespace Drupal\oauth2_client\Service\Grant;

/**
 * Interface for OAuth2 Client Grant Services.
 */
interface Oauth2ClientGrantServiceInterface {

  /**
   * Get an OAuth2 access token.
   *
   * @param string $clientId
   *   The plugin ID of the OAuth2 Client plugin for which an access token
   *   should be retrieved.
   */
  public function getAccessToken($clientId);

}
