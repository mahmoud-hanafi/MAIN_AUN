<?php

namespace Drupal\o365;

use Drupal\Core\StringTranslation\TranslatableMarkup;

/**
 * Interface O365LoggerServiceInterface.
 */
interface O365LoggerServiceInterface {

  /**
   * Log a message into the watchdog.
   *
   * @param \Drupal\Core\StringTranslation\TranslatableMarkup $message
   *   The translatable message.
   * @param string $severity
   *   The severity of the log message.
   */
  public function log(TranslatableMarkup $message, $severity);

  /**
   * Log a debug message into the watchdog.
   *
   * @param \Drupal\Core\StringTranslation\TranslatableMarkup $message
   *   The translatable message.
   */
  public function debug(TranslatableMarkup $message);

}
