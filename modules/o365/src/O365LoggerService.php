<?php

namespace Drupal\o365;

use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Messenger\Messenger;
use Drupal\Core\StringTranslation\TranslatableMarkup;

/**
 * Class O365LoggerService.
 */
class O365LoggerService implements O365LoggerServiceInterface {

  /**
   * Drupal\Core\Logger\LoggerChannelFactoryInterface definition.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactoryInterface
   */
  protected $loggerFactory;

  /**
   * The logger channel.
   *
   * @var \Drupal\Core\Logger\LoggerChannelInterface
   */
  protected $logger;

  /**
   * The drupal messenger.
   *
   * @var \Drupal\Core\Messenger\Messenger
   */
  protected $messenger;

  /**
   * Constructs a new O365LoggerService object.
   *
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   The LoggerChannelFactoryInterface definition.
   * @param \Drupal\Core\Messenger\Messenger $messenger
   *   The Messenger definition.
   */
  public function __construct(LoggerChannelFactoryInterface $logger_factory, Messenger $messenger) {
    $this->loggerFactory = $logger_factory;
    $this->logger = $this->loggerFactory->get('o365');
    $this->messenger = $messenger;
  }

  /**
   * {@inheritdoc}
   */
  public function log(TranslatableMarkup $message, $severity) {
    $this->logger->log($severity, $message);
    $this->showDrupalMessage($message, $severity);
  }

  /**
   * {@inheritdoc}
   */
  public function debug(TranslatableMarkup $message) {
    $this->logger->debug($message);
  }

  /**
   * Render a drupal message.
   *
   * @param \Drupal\Core\StringTranslation\TranslatableMarkup $message
   *   The message to print.
   * @param string $severity
   *   The severity of the message.
   */
  private function showDrupalMessage(TranslatableMarkup $message, $severity) {
    if ($severity === 'error') {
      $this->messenger->addError($message);
    }
    else {
      $this->messenger->addMessage($message);
    }
  }

}
