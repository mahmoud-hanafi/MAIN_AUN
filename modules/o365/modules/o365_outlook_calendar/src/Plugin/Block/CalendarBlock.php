<?php

namespace Drupal\o365_outlook_calendar\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Datetime\DateFormatter;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\o365\HelperService;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\o365\GraphService;

/**
 * Provides a 'CalendarBlock' block.
 *
 * @Block(
 *  id = "calendar_block",
 *  admin_label = @Translation("Calendar block"),
 * )
 */
class CalendarBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * Drupal\o365\GraphService definition.
   *
   * @var \Drupal\o365\GraphService
   */
  protected $o365Graph;

  /**
   * Drupal\o365\HelperService definition.
   *
   * @var \Drupal\o365\HelperService
   */
  protected $helperService;

  /**
   * Drupal\Core\Datetime\DateFormatter definition.
   *
   * @var \Drupal\Core\Datetime\DateFormatter
   */
  protected $dateFormatter;

  /**
   * Constructs a new CalendarBlock object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param string $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\o365\GraphService $o365_graph
   *   The GraphService definition.
   * @param \Drupal\o365\HelperService $helperService
   *   The HelperService definition.
   * @param \Drupal\Core\Datetime\DateFormatter $dateFormatter
   *   The DateFormatter definition.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, GraphService $o365_graph, HelperService $helperService, DateFormatter $dateFormatter) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->o365Graph = $o365_graph;
    $this->helperService = $helperService;
    $this->dateFormatter = $dateFormatter;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static($configuration, $plugin_id, $plugin_definition, $container->get('o365.graph'), $container->get('o365.helpers'), $container->get('date.formatter'));
  }

  /**
   * {@inheritdoc}
   *
   * @throws \Drupal\Core\TempStore\TempStoreException
   * @throws \League\OAuth2\Client\Provider\Exception\IdentityProviderException
   * @throws \Exception
   */
  public function build() {
    $fromDateTimestamp = date('U');
    $toDateTimestamp = strtotime('now + 7 days');
    $selectFromDate = $this->helperService->createIsoDate($fromDateTimestamp);
    $selectEndDate = $this->helperService->createIsoDate($toDateTimestamp);
    $eventData = $this->o365Graph->getGraphData('/me/calendarview?startdatetime=' . $selectFromDate . '&enddatetime=' . $selectEndDate);

    $events = [];
    if (isset($eventData['value']) && !empty($eventData['value'])) {
      foreach ($eventData['value'] as $event) {
        $fromDate = $this->helperService->formatDate($event['start']['dateTime'], $event['start']['timeZone'], 'd F Y H:i');
        $endDate = $this->helperService->formatDate($event['end']['dateTime'], $event['end']['timeZone'], 'd F Y H:i');
        $attendees = t('with @attendees', ['@attendees' => $this->getAttendees($event['attendees'])]);
        $events[] = t('@subject from @fromdate to @enddate @attendees', [
          '@subject' => $event['subject'],
          '@fromdate' => $fromDate,
          '@enddate' => $endDate,
          '@attendees' => $attendees,
        ]);
      }
    }

    return [
      '#theme' => 'item_list',
      '#items' => $events,
    ];
  }

  /**
   * Convert the attendees array to a string of names.
   *
   * @param array $attendees
   *   The attendees array.
   *
   * @return string
   *   The string with names.
   */
  private function getAttendees(array $attendees) {
    if (!empty($attendees)) {
      $list = [];
      foreach ($attendees as $attendee) {
        $list[] = $attendee['emailAddress']['name'];
      }

      return implode(', ', $list);
    }

    return '';
  }

}
