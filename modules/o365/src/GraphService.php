<?php

namespace Drupal\o365;

use Microsoft\Graph\Exception\GraphException;
use Microsoft\Graph\Graph;

/**
 * Class GraphService.
 */
class GraphService {

  /**
   * Drupal\o365\AuthenticationServiceInterface definition.
   *
   * @var \Drupal\o365\AuthenticationServiceInterface
   */
  protected $authService;

  /**
   * The logger service.
   *
   * @var \Drupal\o365\O365LoggerServiceInterface
   */
  protected $messenger;

  /**
   * Constructs a new GraphService object.
   *
   * @param \Drupal\o365\AuthenticationServiceInterface $authenticationService
   *   The AuthenticationServiceInterface definition.
   * @param \Drupal\o365\O365LoggerServiceInterface $messenger
   *   The O365LoggerServiceInterface definition.
   */
  public function __construct(AuthenticationServiceInterface $authenticationService, O365LoggerServiceInterface $messenger) {
    $this->authService = $authenticationService;
    $this->messenger = $messenger;
  }

  /**
   * Get data from the MS GraphAPI.
   *
   * @param string $endpoint
   *   The graph endpoint we want data from.
   * @param string $type
   *   The type of request we want to do.
   * @param bool $raw
   *   This determines if we want a raw body or not.
   * @param string|bool $version
   *   The version of the graph api that is used.
   *
   * @return mixed
   *   The data retrieved from the Graph API.
   *
   * @throws \Drupal\Core\TempStore\TempStoreException
   * @throws \League\OAuth2\Client\Provider\Exception\IdentityProviderException
   */
  public function getGraphData($endpoint, $type = 'GET', $raw = FALSE, $version = FALSE) {
    try {
      $accessToken = $this->authService->getAccessToken();

      $graph = new Graph();

      if ($version) {
        $graph->setApiVersion($version);
      }

      $graph->setAccessToken($accessToken);

      /** @var \Microsoft\Graph\Http\GraphResponse $request */
      $request = $graph->createRequest($type, $endpoint)->execute();

      if ($raw) {
        return $request->getRawBody();
      }

      return $request->getBody();
    }
    catch (GraphException $e) {
      $message = t('Something went wrong: @error', ['@error' => $e->getMessage()]);
      $this->messenger->log($message, 'error');
    }

    return FALSE;
  }

}
