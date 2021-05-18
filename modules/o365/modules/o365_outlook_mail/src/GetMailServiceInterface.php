<?php

namespace Drupal\o365_outlook_mail;

/**
 * Interface GetMailServiceInterface.
 */
interface GetMailServiceInterface {

  /**
   * Get the users mails.
   *
   * @param int $limit
   *   The number of mails to get.
   * @param array $fields
   *   The fields we want to get. If empty all fields will be returned.
   *
   * @return array|bool
   *   The list of mails or FALSE if no mails.
   *
   * @throws \Drupal\Core\TempStore\TempStoreException
   * @throws \League\OAuth2\Client\Provider\Exception\IdentityProviderException
   * @throws \Microsoft\Graph\Exception\GraphException
   */
  public function getMail($limit = 10, array $fields = []);

}
