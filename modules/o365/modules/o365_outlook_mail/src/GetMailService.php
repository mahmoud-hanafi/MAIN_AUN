<?php

namespace Drupal\o365_outlook_mail;

use Drupal\o365\GraphService;

/**
 * Class GetMailService.
 */
class GetMailService implements GetMailServiceInterface {

  /**
   * Drupal\o365\GraphService definition.
   *
   * @var \Drupal\o365\GraphService
   */
  protected $o365Graph;

  /**
   * Constructs a new GetMailService object.
   *
   * @param \Drupal\o365\GraphService $o365_graph
   *   The GraphService definition.
   */
  public function __construct(GraphService $o365_graph) {
    $this->o365Graph = $o365_graph;
  }

  /**
   * {@inheritdoc}
   */
  public function getMail($limit = 10, array $fields = []) {
    $select = '';
    if (!empty($fields)) {
      $select = '$select=' . implode(',', $fields) . '&';
    }

    $mailData = $this->o365Graph->getGraphData('/me/mailFolders/Inbox/messages/delta?' . $select . '$top=' . $limit);

    if (isset($mailData['value']) && !empty($mailData['value'])) {
      return $mailData['value'];
    }

    return FALSE;
  }

}
