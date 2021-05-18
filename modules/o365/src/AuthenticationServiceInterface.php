<?php

namespace Drupal\o365;

/**
 * Interface AuthenticationServiceInterface.
 */
interface AuthenticationServiceInterface {

  /**
   * Redirect the user to the correct Microsoft pages for oAuth2.
   */
  public function loginUser();

  /**
   * Generate and save the accessToken.
   *
   * @param string $code
   *   The code we got from Microsoft.
   * @param mixed $redirect
   *   Either FALSE or a url where to redirect to.
   *
   * @throws \Drupal\Core\TempStore\TempStoreException
   * @throws \League\OAuth2\Client\Provider\Exception\IdentityProviderException
   */
  public function setAccessToken($code, $redirect);

  /**
   * Get the access token for the user.
   *
   * @param bool $login
   *   If we want to login the user if there is no token.
   *
   * @return string
   *   The access token.
   *
   * @throws \Drupal\Core\TempStore\TempStoreException
   * @throws \League\OAuth2\Client\Provider\Exception\IdentityProviderException
   */
  public function getAccessToken($login = FALSE);

  /**
   * Save the auth data from the cookie in the user session storage.
   *
   * @throws \Drupal\Core\TempStore\TempStoreException
   */
  public function saveAuthDataFromCookie();

}
