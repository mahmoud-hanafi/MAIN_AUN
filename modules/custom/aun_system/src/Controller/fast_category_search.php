<?php
namespace Drupal\aun_system\Controller;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Component\Utility\UrlHelper;
use Drupal\node\Entity\Node;
use Drupal\Core\Url;

class fast_category_search extends  ControllerBase{
  public function get_fast_category_search(){
 // print"ss";exit();
    //$content_type = \Drupal::request()->request->get('id');
    $category_id = $_GET['id'];
    $lang = $_GET['lang'];

    //print $content_type;exit();
    //$sql = "SELECT nid , title  FROM node_field_data node WHERE node.type ='$content_type'";
	$sql = "select DISTINCT(link.entity_id) id , field_category_link_uri uri , field_category_link_title title from node__field_category_link link inner join node__field_category_type cat using(entity_id) where cat.field_category_type_value = $category_id and link.langcode = '$lang' ";
    //print $sql;exit();
    $database = \Drupal::database();
    $result = $database->query($sql);
    //print_r($result);exit();
    $i =1;
    $html_code = "<option value='All'> None</option>";
    while ($row_data = $result->fetchAssoc()) {
      $uri = $row_data['uri'];
      $name = $row_data['title'];
      $html_code .= "<option value =". $uri . ">" . $name  . "</option>";
    }
    print $html_code;exit();

  }
}

