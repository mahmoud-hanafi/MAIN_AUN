<?php

namespace Drupal\o365;

use Drupal;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Routing\TrustedRedirectResponse;
use Drupal\Core\TempStore\PrivateTempStoreFactory;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Provider\GenericProvider;
use League\OAuth2\Client\Token\AccessTokenInterface;

/**
 * Class AuthenticationService.
 */
class AuthenticationService implements AuthenticationServiceInterface {

  /**
   * The config factory interface.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The modules API config.
   *
   * @var \Drupal\Core\Config\ImmutableConfig
   */
  protected $apiConfig;

  /**
   * The modules base config.
   *
   * @var \Drupal\Core\Config\ImmutableConfig
   */
  protected $moduleConfig;

  /**
   * The private temp store.
   *
   * @var \Drupal\Core\TempStore\PrivateTempStoreFactory
   */
  protected $tempStore;

  /**
   * A oauth provider.
   *
   * @var \League\OAuth2\Client\Provider\GenericProvider
   */
  protected $oauthClient;

  /**
   * The ConstantsService implementation.
   *
   * @var \Drupal\o365\ConstantsService
   */
  protected $constants;

  /**
   * The logger service.
   *
   * @var \Drupal\o365\O365LoggerServiceInterface
   */
  protected $loggerService;

  /**
   * If we want to add debug messages.
   *
   * @var bool
   */
  private $debug;

  /**
   * The auth data.
   *
   * @var array
   */
  private $authValues = [];

  /**
   * If we want to save the auth as a cookie.
   *
   * @var bool
   */
  public $useCookieAuth = FALSE;

  /**
   * Constructs a new AuthenticationService object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   The config factory interface.
   * @param \Drupal\Core\TempStore\PrivateTempStoreFactory $tempStoreFactory
   *   The private store factory.
   * @param \Drupal\o365\ConstantsService $constantsService
   *   The constants service from the o365 module.
   * @param \Drupal\o365\O365LoggerServiceInterface $loggerService
   *   The logger service from the o365 module.
   */
  public function __construct(ConfigFactoryInterface $configFactory, PrivateTempStoreFactory $tempStoreFactory, ConstantsService $constantsService, O365LoggerServiceInterface $loggerService) {
    $this->configFactory = $configFactory;
    $this->apiConfig = $this->configFactory->get('o365.api_settings');
    $this->moduleConfig = $this->configFactory->get('o365.settings');
    $this->tempStore = $tempStoreFactory;
    $this->constants = $constantsService;
    $this->loggerService = $loggerService;

    $this->debug = !empty($this->moduleConfig->get('verbose_logging'));
  }

  /**
   * {@inheritdoc}
   */
  public function loginUser() {
    if ($this->debug) {
      $message = t('-- loginUser()');
      $this->loggerService->debug($message);
    }

    $clientId = $this->apiConfig->get('client_id');

    if ($this->debug) {
      $message = t('clientId: @client', ['@client' => $clientId]);
      $this->loggerService->debug($message);
    }

    $this->generateProvider();

    $authUrl = $this->oauthClient->getAuthorizationUrl() . '&client_id=' . $clientId;

    $response = new TrustedRedirectResponse($authUrl);
    return $response->send();
  }

  /**
   * {@inheritdoc}
   */
  public function setAccessToken($code, $redirect = FALSE) {
    try {
      // Make the token request.
      $this->generateProvider();
      $accessToken = $this->oauthClient->getAccessToken('authorization_code', [
        'code' => $code,
        'client_id' => $this->apiConfig->get('client_id'),
        'client_secret' => $this->apiConfig->get('client_secret'),
      ]);

      $this->saveAuthData($accessToken);

      if ($redirect) {
        $response = new TrustedRedirectResponse($redirect);
        return $response->send();
      }
    }
    catch (IdentityProviderException $e) {
      $error = $e->getResponseBody();
      $message = t('Error description: @error', ['@error' => $error['error_description']]);
      $this->loggerService->log($message, 'error');
    }

    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function getAccessToken($login = TRUE) {
    $authData = $this->getAuthData();

    if (!empty($authData)) {
      $now = time();

      if ($authData['expires_on'] < $now) {
        return $authData['access_token'];
      }

      if ($this->debug) {
        $message = t('accessToken is expired, refresh the token');
        $this->loggerService->debug($message);
      }

      return $this->refreshToken($authData['refresh_token']);
    }

    if ($login) {
      // We don't have any auth data, so redirect to login and back.
      $this->loginUser();
    }

    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function saveAuthDataFromCookie() {
    $authCookie = $_COOKIE[$this->constants->getUserTempStoreDataName()];
    $authValues = json_decode($authCookie, TRUE);

    // Save the data and delete the cookie.
    $this->saveDataToTempStore($this->constants->getUserTempStoreDataName(), $authValues);
    setcookie($this->constants->getUserTempStoreDataName(), '', time() - 3600);

    $this->useCookieAuth = FALSE;
  }

  /**
   * Generate a new access token based on the refresh token.
   *
   * @param string $refreshToken
   *   The refresh token.
   *
   * @return string
   *   The new access token.
   *
   * @throws \Drupal\Core\TempStore\TempStoreException
   * @throws \League\OAuth2\Client\Provider\Exception\IdentityProviderException
   */
  private function refreshToken($refreshToken) {
    $this->generateProvider();

    $accessToken = $this->oauthClient->getAccessToken('refresh_token', [
      'refresh_token' => $refreshToken,
      'client_id' => $this->apiConfig->get('client_id'),
      'client_secret' => $this->apiConfig->get('client_secret'),
    ]);
    $this->saveAuthData($accessToken);
    return $accessToken->getToken();
  }

  /**
   * Generate a basic oAuth2 provider.
   */
  private function generateProvider() {
    $redirectUri = empty($this->apiConfig->get('redirect_callback')) ? 'https://' . Drupal::request()->getHost() . '/o365/callback' : $this->apiConfig->get('redirect_callback');
    $scopes = $this->apiConfig->get('auth_scopes');

    $providerData = [
      'client_id' => $this->apiConfig->get('client_id'),
      'client_secret' => $this->apiConfig->get('client_secret'),
      'redirectUri' => $redirectUri,
      'urlAuthorize' => $this->constants->getAuthorizeUrl(),
      'urlAccessToken' => $this->constants->getTokenUrl(),
      'urlResourceOwnerDetails' => '',
      'scopes' => (strpos($scopes, 'offline_access') !== FALSE) ? $scopes : 'offline_access ' . $scopes,
    ];

    if ($this->debug) {
      $message = t('providerData: @data', ['@data' => print_r($providerData, 1)]);
      $this->loggerService->debug($message);
    }

    $this->oauthClient = new GenericProvider($providerData);
  }

  /**
   * Get the auth data from temp store or cookie.
   *
   * @return array
   *   The saved auth data.
   */
  private function getAuthData() {
    if ($this->useCookieAuth) {
      $authCookie = $_COOKIE[$this->constants->getUserTempStoreDataName()];
      return json_decode($authCookie, TRUE);
    }

    $tempstore = $this->tempStore->get($this->constants->getUserTempStoreName());
    return $tempstore->get($this->constants->getUserTempStoreDataName());
  }

  /**
   * Save the auth data to the temp store.
   *
   * @param \League\OAuth2\Client\Token\AccessTokenInterface $accessToken
   *   The access token object.
   *
   * @throws \Drupal\Core\TempStore\TempStoreException
   */
  private function saveAuthData(AccessTokenInterface $accessToken) {
    $this->authValues = [
      'access_token' => $accessToken->getToken(),
      'refresh_token' => $accessToken->getRefreshToken(),
      'expires_on' => time() + $accessToken->getExpires(),
    ];

    if ($this->debug) {
      $message = t('Saving authData: @data', ['@data' => print_r($this->authValues, 1)]);
      $this->loggerService->debug($message);
    }

    $this->saveDataToTempStore($this->constants->getUserTempStoreDataName(), $this->authValues);
    setcookie($this->constants->getUserTempStoreDataName(), json_encode($this->authValues), $this->constants->getCookieExpire(), '/');
  }

  /**
   * Save data to the tempstore.
   *
   * @param string $name
   *   The name of the store.
   * @param mixed $value
   *   The value.
   *
   * @throws \Drupal\Core\TempStore\TempStoreException
   */
  private function saveDataToTempStore($name, $value) {
    $tempstore = $this->tempStore->get($this->constants->getUserTempStoreName());

    $tempstore->set($name, $value);
  }

}
