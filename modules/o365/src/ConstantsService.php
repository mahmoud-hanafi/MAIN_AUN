<?php

namespace Drupal\o365;

use Drupal;

/**
 * Class ConstantsService.
 */
class ConstantsService {

  /**
   * The url where Microsoft will redirect us too.
   *
   * @var string
   */
  private $redirectUrl = '/o365/callback';

  /**
   * The authorize endpoint.
   *
   * @var string
   */
  private $authorizeUrl = 'https://login.microsoftonline.com/common/oauth2/v2.0/authorize';

  /**
   * The token endpoint.
   *
   * @var string
   */
  private $tokenUrl = 'https://login.microsoftonline.com/common/oauth2/v2.0/token';

  /**
   * The name of the temp store.
   *
   * @var string
   */
  private $userTempStoreName = 'o365.tempstore';

  /**
   * The name of the data saved in the temp store.
   *
   * @var string
   */
  private $userTempStoreDataName = 'o365AuthData';

  /**
   * Get the redirect URL.
   *
   * @return string
   *   The redirect url.
   */
  public function getRedirectUrl() {
    return 'https://' . Drupal::request()->getHost() . $this->redirectUrl;
  }

  /**
   * Get the authorize url.
   *
   * @return string
   *   The authorize url.
   */
  public function getAuthorizeUrl() {
    return $this->authorizeUrl;
  }

  /**
   * Get the token url.
   *
   * @return string
   *   The token url.
   */
  public function getTokenUrl() {
    return $this->tokenUrl;
  }

  /**
   * Get the user temp store name.
   *
   * @return string
   *   The user temp store name.
   */
  public function getUserTempStoreName() {
    return $this->userTempStoreName;
  }

  /**
   * Get the user temp store data name.
   *
   * @return string
   *   The user temp store data name.
   */
  public function getUserTempStoreDataName() {
    return $this->userTempStoreDataName;
  }

  /**
   * Get the cookie expire timestamp.
   *
   * @return int
   *   The expire timestamp.
   */
  public function getCookieExpire() {
    return time() + 3600;
  }

}
