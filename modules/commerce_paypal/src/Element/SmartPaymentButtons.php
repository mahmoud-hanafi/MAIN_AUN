<?php

namespace Drupal\commerce_paypal\Element;

use Drupal\commerce_paypal\Plugin\Commerce\PaymentGateway\CheckoutInterface;
use Drupal\Component\Utility\Html;
use Drupal\Core\Render\Element\RenderElement;
use Drupal\Core\Render\Markup;
use Drupal\Core\Template\Attribute;
use Drupal\Core\Url;

/**
 * Smart payment buttons render element.
 *
 * @RenderElement("commerce_paypal_smart_payment_buttons")
 */
class SmartPaymentButtons extends RenderElement {

  /**
   * {@inheritdoc}
   */
  public function getInfo() {
    $class = get_class($this);
    return [
      '#html_id' => 'paypal-buttons-container',
      '#commit' => FALSE,
      '#order' => NULL,
      '#payment_gateway' => NULL,
      '#pre_render' => [
        [$class, 'preRender'],
      ],
    ];
  }

  /**
   * Pre-render callback.
   *
   * @return array
   *   The $element
   */
  public static function preRender($element) {
    if (empty($element['#payment_gateway']) || empty($element['#html_id']) || empty($element['#order'])) {
      return $element;
    }
    /**
     * @var \Drupal\commerce_payment\Entity\PaymentGatewayInterface $payment_gateway
     */
    $payment_gateway = $element['#payment_gateway'];
    if (!$payment_gateway->getPlugin() instanceof CheckoutInterface) {
      return $element;
    }
    $configuration = $payment_gateway->getPluginConfiguration();

    if (empty($configuration['client_id'])) {
      return $element;
    }
    /**
     * @var \Drupal\commerce_order\Entity\OrderInterface $order
     */
    $order = $element['#order'];

    $attributes = new Attribute();
    $attributes
      ->setAttribute('id', Html::getUniqueId($element['#html_id']))
      ->setAttribute('class', 'paypal-buttons-container');

    $route_options = [
      'commerce_payment_gateway' => $payment_gateway->id(),
      'commerce_order' => $order->id(),
    ];
    $options = [
      'query' => [
        'client-id' => $configuration['client_id'],
        'intent' => $configuration['intent'],
        'commit' => $element['#commit'] ? 'true' : 'false',
        'currency' => $order->getTotalPrice()->getCurrencyCode(),
      ],
    ];
    if (!empty($configuration['disable_funding'])) {
      $options['query']['disable-funding'] = implode(',', $configuration['disable_funding']);
    }
    if (!empty($configuration['disable_card'])) {
      $options['query']['disable-card'] = implode(',', $configuration['disable_card']);
    }
    $element['#attached']['library'][] = 'commerce_paypal/paypal_checkout';
    $element['#attached']['drupalSettings']['paypalCheckout'] = [
      'src' => Url::fromUri('https://www.paypal.com/sdk/js', $options)->toString(),
      'elementSelector' => '.paypal-buttons-container',
      'onCreateUri' => Url::fromRoute('commerce_paypal.checkout.create', $route_options)->toString(),
      'onApproveUri' => Url::fromRoute('commerce_paypal.checkout.approve', $route_options)->toString(),
      'style' => $configuration['style'],
    ];
    $element['#markup'] = Markup::create(sprintf('<div %s></div>', $attributes));
    return $element;
  }

}
