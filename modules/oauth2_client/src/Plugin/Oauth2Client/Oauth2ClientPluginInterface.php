<?php

namespace Drupal\oauth2_client\Plugin\Oauth2Client;

use Drupal\Component\Plugin\PluginInspectionInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;

/**
 * Interface for Oauth2 Client plugins.
 */
interface Oauth2ClientPluginInterface extends PluginInspectionInterface, ContainerFactoryPluginInterface {

  /**
   * Retrieves the human-readable name of the Oauth2 Client plugin.
   *
   * @return string
   *   The name of the plugin.
   */
  public function getName();

  /**
   * Retrieves the id of the OAuth2 Client plugin.
   *
   * @return string
   *   The id of the plugin.
   */
  public function getId();

  /**
   * Retrieves the grant type of the plugin.
   *
   * @return string
   *   Possible values:
   *   - authorization_code
   *   - client_credentials
   *   - refresh_toekn
   *   - resource_owner
   */
  public function getGrantType();

  /**
   * Retrieves the client_id of the OAuth2 server.
   *
   * @return string
   *   The client_id of the OAuth2 server.
   */
  public function getClientId();

  /**
   * Retrieves the client_secret of the OAuth2 server.
   *
   * @return string
   *   The client_secret of the OAuth2 server.
   */
  public function getClientSecret();

  /**
   * Retrieves the redirect_uri of the OAuth2 server.
   *
   * @return string
   *   The redirect_uri of the OAuth2 server.
   */
  public function getRedirectUri();

  /**
   * Retrieves the authorization_uri of the OAuth2 server.
   *
   * @return string
   *   The authorization_uri of the OAuth2 server.
   */
  public function getAuthorizationUri();

  /**
   * Retrieves the token_uri of the OAuth2 server.
   *
   * @return string
   *   The authorization_uri of the OAuth2 server.
   */
  public function getTokenUri();

  /**
   * Retrieves the resource_uri of the OAuth2 server.
   *
   * @return string
   *   The resource_uri of the OAuth2 server.
   */
  public function getResourceUri();

  /**
   * Retrieves the username for the account to authenticate with.
   *
   * @return string
   *   The username of the account to authenticate with.
   */
  public function getUsername();

  /**
   * Retrieves the password for the account to authenticate with.
   *
   * @return string
   *   The password for the account to authenticate with.
   */
  public function getPassword();

}
