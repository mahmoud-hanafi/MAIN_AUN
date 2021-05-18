<?php

namespace Drupal\o365_sso\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\RouteCollection;

/**
 * Class UserLoginFormRouteSubscriber.
 *
 * @package Drupal\o365_sso\Routing
 */
class UserLoginFormRouteSubscriber extends RouteSubscriberBase {

  /**
   * {@inheritdoc}
   */
  protected function alterRoutes(RouteCollection $collection) {
    if ($route = $collection->get('user.login')) {
      $route->setDefault('_form', 'Drupal\o365_sso\Form\UserLoginFormAlter');
    }
  }

}
