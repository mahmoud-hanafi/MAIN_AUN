<?php

namespace Drupal\o365_onedrive\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\o365_onedrive\GetFilesAndFoldersServiceInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class OndeDriveListController.
 */
class OneDriveListController extends ControllerBase {

  /**
   * The get files and folders service.
   *
   * @var \Drupal\o365_onedrive\GetFilesAndFoldersServiceInterface
   */
  protected $getFilesAndFoldersService;

  /**
   * Constructs a new OneDriveListController object.
   *
   * @param \Drupal\o365_onedrive\GetFilesAndFoldersServiceInterface $getFilesAndFoldersService
   *   The GetFilesAndFoldersServiceInterface definition.
   */
  public function __construct(GetFilesAndFoldersServiceInterface $getFilesAndFoldersService) {
    $this->getFilesAndFoldersService = $getFilesAndFoldersService;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static($container->get('o365_onedrive.get_files'));
  }

  /**
   * Render a list of files and folders.
   *
   * @param bool $folder
   *   The folder ID or false.
   *
   * @return mixed
   *   The list of files and folders.
   *
   * @throws \Drupal\Core\TempStore\TempStoreException
   * @throws \League\OAuth2\Client\Provider\Exception\IdentityProviderException
   * @throws \Microsoft\Graph\Exception\GraphException
   */
  public function listFiles($folder = FALSE) {
    return $this->getFilesAndFoldersService->listFilesAndFolders($folder);
  }

  /**
   * Create a list of shared files and folders.
   *
   * @return array|mixed
   *   The list of files and folders.
   *
   * @throws \Drupal\Core\TempStore\TempStoreException
   * @throws \League\OAuth2\Client\Provider\Exception\IdentityProviderException
   * @throws \Microsoft\Graph\Exception\GraphException
   */
  public function listSharedFiles() {
    return $this->getFilesAndFoldersService->listSharedFilesAndFolders();
  }

}
