<?php

namespace Drupal\o365_outlook_mail\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\o365_outlook_mail\GetMailServiceInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a 'LatestMailBlock' block.
 *
 * @Block(
 *  id = "latest_mail_block",
 *  admin_label = @Translation("Latest mail block"),
 * )
 */
class LatestMailBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The mail service interface.
   *
   * @var \Drupal\o365_outlook_mail\GetMailServiceInterface
   */
  protected $getMailService;

  /**
   * Constructs a new LatestMailBlock object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param string $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\o365_outlook_mail\GetMailServiceInterface $getMailService
   *   The get mail service definition.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, GetMailServiceInterface $getMailService) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->getMailService = $getMailService;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static($configuration, $plugin_id, $plugin_definition, $container->get('o365_outlook_mail.get_mail'));
  }

  /**
   * {@inheritdoc}
   *
   * @throws \Drupal\Core\TempStore\TempStoreException
   * @throws \League\OAuth2\Client\Provider\Exception\IdentityProviderException
   * @throws \Microsoft\Graph\Exception\GraphException
   */
  public function build() {
    return $this->getMails();
  }

  /**
   * Get and normalize a list of mails.
   *
   * @param int $count
   *   The number of mails to show.
   *
   * @return mixed
   *   The item list or FALSE.
   *
   * @throws \Drupal\Core\TempStore\TempStoreException
   * @throws \League\OAuth2\Client\Provider\Exception\IdentityProviderException
   * @throws \Microsoft\Graph\Exception\GraphException
   */
  private function getMails($count = 10) {
    $mailData = $this->getMailService->getMail($count);

    if ($mailData) {
      $items = [];
      foreach ($mailData as $mail) {
        $items[] = $mail['subject'] . ' -- ' . $mail['receivedDateTime'];
      }

      return [
        '#theme' => 'item_list',
        '#items' => $items,
      ];
    }

    return FALSE;
  }

}
