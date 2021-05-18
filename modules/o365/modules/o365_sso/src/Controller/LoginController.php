<?php

namespace Drupal\o365_sso\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\o365\AuthenticationService;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class LoginController.
 */
class LoginController extends ControllerBase {

  /**
   * The authentication service, used to handle all kinds of auth stuff.
   *
   * @var \Drupal\o365\AuthenticationService
   */
  protected $authenticationService;

  /**
   * Constructs a new LoginController object.
   *
   * @param \Drupal\o365\AuthenticationService $authenticationService
   *   The AuthenticationService definition.
   */
  public function __construct(AuthenticationService $authenticationService) {
    $this->authenticationService = $authenticationService;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('o365.authentication')
    );
  }

  /**
   * Login.
   *
   * @return mixed
   *   Return the data.
   */
  public function login() {
    return $this->authenticationService->loginUser();
  }

}
