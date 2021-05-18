<?php

namespace Drupal\o365;

use DateTime;
use DateTimeZone;
use Drupal\Core\Datetime\DateFormatterInterface;

/**
 * Class HelperService.
 */
class HelperService {

  /**
   * Drupal\Core\Datetime\DateFormatterInterface definition.
   *
   * @var \Drupal\Core\Datetime\DateFormatterInterface
   */
  protected $dateFormatter;

  /**
   * Constructs a new HelperService object.
   *
   * @param \Drupal\Core\Datetime\DateFormatterInterface $date_formatter
   *   The DateFormatterInterface definition.
   */
  public function __construct(DateFormatterInterface $date_formatter) {
    $this->dateFormatter = $date_formatter;
  }

  /**
   * Create a ISO8601 timestamp that Microsoft Graph API can use.
   *
   * @param int $timestamp
   *   The unix timestamp we want to use to create the date.
   *
   * @return string
   *   The ISO8601 formatted date.
   */
  public function createIsoDate($timestamp) {
    return $this->dateFormatter->format($timestamp, 'custom', 'Y-m-d\TH:i:s.000') . 'Z';
  }

  /**
   * Formate a ISO8601 date into something more readable.
   *
   * @param string $date
   *   The ISO8601 date.
   * @param string $timezone
   *   The timezone.
   * @param string $format
   *   The format we want to convert to.
   *
   * @return string
   *   The formatted date.
   *
   * @throws \Exception
   */
  public function formatDate($date, $timezone = 'UTC', $format = 'd-m-Y H:i') {
    $dateTimezone = new DateTimeZone($timezone);
    $dateTime = new DateTime($date, $dateTimezone);

    return $this->dateFormatter->format($dateTime->format('U'), 'custom', $format);
  }

}
