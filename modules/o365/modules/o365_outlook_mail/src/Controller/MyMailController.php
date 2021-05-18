<?php

namespace Drupal\o365_outlook_mail\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\o365\HelperService;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\o365_outlook_mail\GetMailServiceInterface;

/**
 * Class MyMailController.
 */
class MyMailController extends ControllerBase {

  /**
   * Drupal\o365_outlook_mail\GetMailServiceInterface definition.
   *
   * @var \Drupal\o365_outlook_mail\GetMailServiceInterface
   */
  protected $getMailService;

  /**
   * The o365 helper service with handy methods.
   *
   * @var \Drupal\o365\HelperService
   */
  protected $helperService;

  /**
   * Constructs a new MyMailController object.
   *
   * @param \Drupal\o365_outlook_mail\GetMailServiceInterface $getMailService
   *   The GetMailServiceInterface definition.
   * @param \Drupal\o365\HelperService $helperService
   * The HelperService definition.
   */
  public function __construct(GetMailServiceInterface $getMailService, HelperService $helperService) {
    $this->getMailService = $getMailService;
    $this->helperService = $helperService;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('o365_outlook_mail.get_mail'),
      $container->get('o365.helpers')
    );
  }

  /**
   * Get the latest mails of the user.
   *
   * @return array
   *   The render array with the list of mails.
   *
   * @throws \Drupal\Core\TempStore\TempStoreException
   * @throws \League\OAuth2\Client\Provider\Exception\IdentityProviderException
   * @throws \Microsoft\Graph\Exception\GraphException
   * @throws \Exception
   */
  public function getMail() {
    $mailData = $this->getMailService->getMail(20);
    $tableHeader = [
      $this->t('Subject'),
      $this->t('From'),
      $this->t('Date'),
    ];
    $tableRows = [];

    if ($mailData) {
      $subjectUrlOptions = [
        'attributes' => [
          'target' => '_blank',
        ],
      ];

      foreach ($mailData as $mail) {
        $subjectUrl = Url::fromUri($mail['webLink'], $subjectUrlOptions);
        $mailtoUrl = Url::fromUri('mailto:' . $mail['from']['emailAddress']['address']);
        $tableRow = [
          Link::fromTextAndUrl($mail['subject'], $subjectUrl),
          Link::fromTextAndUrl($mail['from']['emailAddress']['name'], $mailtoUrl),
          $this->helperService->formatDate($mail['receivedDateTime']),
        ];

        // Set the row class to rwad or unread based on the status of the email.
        $rowClass = 'unread';
        if ($mail['isRead']) {
          $rowClass = 'read';
        }

        $tableRows[] = [
          'data' => $tableRow,
          'class' => $rowClass,
        ];
      }
    }

    $data = [
      '#type' => 'table',
      '#header' => $tableHeader,
      '#rows' => $tableRows,
      '#empty' => $this->t('No emails have been found'),
    ];
    $data['#attached']['library'][] = 'o365_outlook_mail/o365_outlook_mail';

    return $data;
  }

}
