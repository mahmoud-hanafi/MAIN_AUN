<?php

namespace Drupal\commerce_paypal\Plugin\Commerce\PaymentGateway;

use Drupal\commerce_order\Entity\OrderInterface;
use Drupal\commerce_payment\Plugin\Commerce\PaymentGateway\OnsitePaymentGatewayInterface;
use Drupal\commerce_payment\Plugin\Commerce\PaymentGateway\SupportsAuthorizationsInterface;
use Drupal\commerce_payment\Plugin\Commerce\PaymentGateway\SupportsRefundsInterface;

/**
 * Provides the interface for the Checkout payment gateway.
 */
interface CheckoutInterface extends OnsitePaymentGatewayInterface, SupportsAuthorizationsInterface, SupportsRefundsInterface {

  /**
   * Create/update the payment method when the payment is approved on PayPal.
   *
   * This is called by the onApprove JS SDK callback.
   *
   * @param \Drupal\commerce_order\Entity\OrderInterface $order
   *   The order.
   * @param array $paypal_order
   *   The PayPal order.
   *
   * @return \Symfony\Component\HttpFoundation\Response
   *   A response.
   */
  public function onApprove(OrderInterface $order, array $paypal_order);

}
