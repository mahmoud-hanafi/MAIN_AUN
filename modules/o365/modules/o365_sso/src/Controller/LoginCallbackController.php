<?php

namespace Drupal\o365_sso\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;
use Drupal\o365\AuthenticationService;
use Drupal\o365\GraphService;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class LoginCallbackController.
 */
class LoginCallbackController extends ControllerBase {

  /**
   * The authentication service, used to handle all kinds of auth stuff.
   *
   * @var \Drupal\o365\AuthenticationService
   */
  protected $authenticationService;

  /**
   * The o365 GraphService.
   *
   * @var \Drupal\o365\GraphService
   */
  protected $graphService;

  /**
   * Constructs a new LoginController object.
   *
   * @param \Drupal\o365\AuthenticationService $authenticationService
   *   The AuthenticationService definition.
   * @param \Drupal\o365\GraphService $graphService
   *   The GraphService definition.
   */
  public function __construct(AuthenticationService $authenticationService, GraphService $graphService) {
    $this->authenticationService = $authenticationService;
    $this->graphService = $graphService;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('o365.authentication'),
      $container->get('o365.graph')
    );
  }

  /**
   * Callback for the login.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request.
   *
   * @return mixed
   *   A redirect to the set URL.
   *
   * @throws \Drupal\Core\TempStore\TempStoreException
   * @throws \League\OAuth2\Client\Provider\Exception\IdentityProviderException
   */
  public function callback(Request $request) {
    $authCode = $request->get('code');

    if ($authCode) {
      $redirectUrl = Url::fromRoute('o365_sso.user_login_controller_login');
      $this->authenticationService->setAccessToken($authCode, $redirectUrl->toString());
    }

    return [
      '#type' => 'markup',
      '#markup' => $this->t('The authorisation code has not been provided. Please try again.'),
    ];
  }

}
