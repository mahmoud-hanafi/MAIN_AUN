<?php
namespace Drupal\aun_system\Controller;
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Component\Utility\Xss;
use Drupal\Core\Entity\Element\EntityAutocomplete;
/**
 * Defines a route controller for watches autocomplete form elements.
 */
class sub_category extends ControllerBase {

  public function handle_sub_category(){
    $sql = "SELECT nid , title FROM node_field_data WHERE type ='news'";
    //print $sql;exit();
    $database = \Drupal::database();
    $result = $database->query($sql);
    $i =1;
    while ($row_data = $result->fetchAssoc()) {
      $nid = $row_data['nid'];
      $name = $row_data['title']
      $html_code .="<option value =". $nid . ">" . $name  . "</option>";
    }
    echo $html_code;
  }
}
