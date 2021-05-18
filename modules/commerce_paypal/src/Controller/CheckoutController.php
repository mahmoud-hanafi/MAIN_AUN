<?php

namespace Drupal\commerce_paypal\Controller;

use Drupal\commerce_order\Entity\OrderInterface;
use Drupal\commerce_payment\Entity\PaymentGatewayInterface;
use Drupal\commerce_paypal\CheckoutSdkFactoryInterface;
use Drupal\commerce_paypal\Plugin\Commerce\PaymentGateway\CheckoutInterface;
use Drupal\Component\Serialization\Json;
use Drupal\Core\Access\AccessException;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use GuzzleHttp\Exception\ClientException;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * PayPal checkout controller.
 */
class CheckoutController extends ControllerBase {

  /**
   * The PayPal Checkout SDK factory.
   *
   * @var \Drupal\commerce_paypal\CheckoutSdkFactoryInterface
   */
  protected $checkoutSdkFactory;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   */
  protected $entityTypeManager;

  /**
   * The logger.
   *
   * @var \Psr\Log\LoggerInterface $logger
   */
  protected $logger;

  /**
   * Constructs a PayPalCheckoutController object.
   *
   * @param \Drupal\commerce_paypal\CheckoutSdkFactoryInterface $checkout_sdk_factory
   *   The PayPal Checkout SDK factory.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Psr\Log\LoggerInterface $logger
   *   The logger.
   */
  public function __construct(CheckoutSdkFactoryInterface $checkout_sdk_factory, EntityTypeManagerInterface $entity_type_manager, LoggerInterface $logger) {
    $this->checkoutSdkFactory = $checkout_sdk_factory;
    $this->entityTypeManager = $entity_type_manager;
    $this->logger = $logger;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('commerce_paypal.checkout_sdk_factory'),
      $container->get('entity_type.manager'),
      $container->get('logger.channel.commerce_paypal')
    );
  }

  /**
   * Create/update the order in PayPal.
   *
   * @param PaymentGatewayInterface $commerce_payment_gateway
   *   The payment gateway.
   * @param \Drupal\commerce_order\Entity\OrderInterface $commerce_order
   *   The order.
   *
   * @return \Symfony\Component\HttpFoundation\Response
   *   A response.
   */
  public function onCreate(PaymentGatewayInterface $commerce_payment_gateway, OrderInterface $commerce_order) {
    $payment_gateway_plugin = $commerce_payment_gateway->getPlugin();
    if (!$payment_gateway_plugin instanceof CheckoutInterface) {
      throw new AccessException('Invalid payment gateway provided.');
    }
    $config = $commerce_payment_gateway->getPluginConfiguration();
    $sdk = $this->checkoutSdkFactory->get($config);
    /**
     * @var \Drupal\commerce_payment\Entity\PaymentMethodInterface|NULL $payment_method;
     */
    $payment_method = !$commerce_order->get('payment_method')->isEmpty() ? $commerce_order->get('payment_method')->entity : NULL;
    try {
      $response = $sdk->createOrder($commerce_order);
      $body = Json::decode($response->getBody()->getContents());

      // If order was already referencing a payment method, update it.
      if (!empty($payment_method) && $payment_method->bundle() == 'paypal_checkout') {
        $payment_method->setRemoteId($body['id']);
        $payment_method->save();
      }

      return new JsonResponse(['id' => $body['id']]);
    }
    catch (ClientException $exception) {
      $this->logger->error($exception->getMessage());
      return new Response('', Response::HTTP_BAD_REQUEST);
    }
  }

  /**
   * React to the PayPal checkout "onApprove" JS SDK callback.
   *
   * @param PaymentGatewayInterface $commerce_payment_gateway
   *   The payment gateway.
   * @param \Drupal\commerce_order\Entity\OrderInterface $commerce_order
   *   The order.
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request.
   *
   * @return \Symfony\Component\HttpFoundation\Response
   *   A response.
   */
  public function onApprove(PaymentGatewayInterface $commerce_payment_gateway, OrderInterface $commerce_order, Request $request) {
    $payment_gateway_plugin = $commerce_payment_gateway->getPlugin();
    if (!$payment_gateway_plugin instanceof CheckoutInterface) {
      throw new AccessException('Invalid payment gateway provided.');
    }
    $body = Json::decode($request->getContent());
    if (!isset($body['id'])) {
      throw new AccessException('Missing PayPal order ID.');
    }
    $config = $payment_gateway_plugin->getConfiguration();
    $sdk = $this->checkoutSdkFactory->get($config);

    try {
      $request = $sdk->getOrder($body['id']);
      $paypal_order = Json::decode($request->getBody()->getContents());
      $response = $payment_gateway_plugin->onApprove($commerce_order, $paypal_order);
    }
    catch (ClientException $exception) {
      $this->logger->error($exception->getMessage());
      throw new AccessException('Could not load the order from PayPal.');
    }

    if (empty($response)) {
      $response = new Response('', 200);
    }

    return $response;
  }

}
