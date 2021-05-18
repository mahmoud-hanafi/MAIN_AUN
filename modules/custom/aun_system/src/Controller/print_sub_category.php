<?php
namespace Drupal\aun_system\Controller;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Component\Utility\UrlHelper;

class print_test_message extends  ControllerBase{
  public function print_msg(){
    return array(
      '#title' => 'Hello World!',
      '#markup' => 'Here is some content.',
    );
  }

  public function print_form(){

    return array(
      '#title' => 'Hello World!',
      '#markup' => 'Here is some content.',
    );
  }
}

