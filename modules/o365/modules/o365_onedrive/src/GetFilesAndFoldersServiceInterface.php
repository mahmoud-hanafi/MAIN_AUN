<?php

namespace Drupal\o365_onedrive;

/**
 * Interface GetFilesAndFoldersServiceInterface.
 */
interface GetFilesAndFoldersServiceInterface {

  /**
   * List all files and folders.
   *
   * @param mixed $folder
   *   The ID of a folder or FALSE for the root.
   *
   * @return mixed
   *   The list of files and folders.
   *
   * @throws \Drupal\Core\TempStore\TempStoreException
   * @throws \League\OAuth2\Client\Provider\Exception\IdentityProviderException
   * @throws \Microsoft\Graph\Exception\GraphException
   */
  public function listFilesAndFolders($folder = FALSE);

  /**
   * List all the shared files and folders.
   *
   * @return mixed
   */

  /**
   * List all the shared files and folders.
   *
   * @param int $limit
   *   The max amount of items to return.
   *
   * @return array|mixed
   *   The list of files and folders.
   *
   * @throws \Drupal\Core\TempStore\TempStoreException
   * @throws \League\OAuth2\Client\Provider\Exception\IdentityProviderException
   * @throws \Microsoft\Graph\Exception\GraphException
   */
  public function listSharedFilesAndFolders($limit = 10);

  /**
   * Get a list of special files and folders. For instance the recent files.
   *
   * @param string $type
   *   The special type.
   *
   * @return array|mixed
   *   The list of files and folders.
   *
   * @throws \Drupal\Core\TempStore\TempStoreException
   * @throws \League\OAuth2\Client\Provider\Exception\IdentityProviderException
   * @throws \Microsoft\Graph\Exception\GraphException
   */
  public function listSpecialFilesAndFolders($type);

}
