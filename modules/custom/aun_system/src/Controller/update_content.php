<?php
namespace Drupal\aun_system\Controller;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Component\Utility\UrlHelper;
use Drupal\node\Entity\Node;
use Drupal\Core\Url;

class update_content extends  ControllerBase{
  public function handle_update_content(){
	global $base_url;
	$sql = "SELECT nid FROM node_field_data WHERE `type` in ('awards','news','page','research','event','faculty_department')  order by nid asc " ;
    $database = \Drupal::database();
    $result = $database->query($sql);
    $i =1;
    while ($row_data = $result->fetchAssoc()) {
	  $node_nid = $row_data['nid'];
	  $node = Node::load($node_nid);
	  $node->field_social_share->value = 1;
	  $node->save();
	  print "$node_nid -  $i <br>";
	  $i++;
    }
	exit();
  }
}

