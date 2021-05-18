<?php

namespace Drupal\social_media\Event;

use Symfony\Component\EventDispatcher\Event;

/**
 * Class SocialMediaEvent.
 */
class SocialMediaEvent extends Event {

  /**
   * TODO describe element.
   *
   * @var array
   */
  protected $element;

  /**
   * Constructor.
   *
   * @param array $element
   *   TODO describe what element is.
   */
  public function __construct(array $element) {
    $this->element = $element;
  }

  /**
   * Return the element.
   *
   * @return array
   *   The element.
   */
  public function getElement() {
    return $this->element;
  }

  /**
   * Element setter.
   *
   * @param array $element
   *   TODO describe what element is.
   */
  public function setElement(array $element) {
    $this->element = $element;
  }

}
