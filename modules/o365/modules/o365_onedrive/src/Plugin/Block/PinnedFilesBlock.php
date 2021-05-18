<?php

namespace Drupal\o365_onedrive\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\o365_onedrive\GetFilesAndFoldersServiceInterface;

/**
 * Provides a 'PinnedFilesBlock' block.
 *
 * @Block(
 *  id = "pinned_files_block",
 *  admin_label = @Translation("Pinned files block"),
 * )
 */
class PinnedFilesBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * Drupal\o365_onedrive\GetFilesAndFoldersServiceInterface definition.
   *
   * @var \Drupal\o365_onedrive\GetFilesAndFoldersServiceInterface
   */
  protected $o365OnedriveGetFiles;

  /**
   * Constructs a new PinnedFilesBlock object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param string $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\o365_onedrive\GetFilesAndFoldersServiceInterface $o365_onedrive_get_files
   *   The GetFilesAndFoldersServiceInterface definition.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, GetFilesAndFoldersServiceInterface $o365_onedrive_get_files) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->o365OnedriveGetFiles = $o365_onedrive_get_files;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static($configuration, $plugin_id, $plugin_definition, $container->get('o365_onedrive.get_files'));
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = [];
    $build['#theme'] = 'pinned_files_block';
    $build['pinned_files_block']['#markup'] = 'Not yet possible in the MS Graph API.';

    return $build;
  }

}
