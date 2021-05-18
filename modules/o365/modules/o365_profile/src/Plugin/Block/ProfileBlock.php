<?php

namespace Drupal\o365_profile\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\o365\GraphService;

/**
 * Provides a 'ProfileBlock' block.
 *
 * @Block(
 *  id = "profile_block",
 *  admin_label = @Translation("Office 365 Profile block"),
 * )
 */
class ProfileBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * Drupal\o365\GraphService definition.
   *
   * @var \Drupal\o365\GraphService
   */
  protected $o365Graph;

  /**
   * Constructs a new ProfileBlock object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param string $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\o365\GraphService $o365_graph
   *   The GraphService definition.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, GraphService $o365_graph) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->o365Graph = $o365_graph;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static($configuration, $plugin_id, $plugin_definition, $container->get('o365.graph'));
  }

  /**
   * {@inheritdoc}
   *
   * @throws \Drupal\Core\TempStore\TempStoreException
   * @throws \League\OAuth2\Client\Provider\Exception\IdentityProviderException
   */
  public function build() {
    $userData = $this->o365Graph->getGraphData('/me');
    $imageData = $this->o365Graph->getGraphData('/me/photo/$value', 'GET', TRUE);
    $imageMeta = $this->o365Graph->getGraphData('/me/photo');
    $imageSrc = 'data:' . $imageMeta['@odata.mediaContentType'] . ';base64,' . base64_encode($imageData);

    $build = [
      '#theme'       => 'o365_profile_block',
      '#displayName' => $userData['displayName'],
      '#data'        => $userData,
      '#imageData'   => $imageSrc,
      '#attached' => [
        'library' => [
          'o365_profile/profile_block',
        ],
      ],
    ];

    return $build;
  }

}
