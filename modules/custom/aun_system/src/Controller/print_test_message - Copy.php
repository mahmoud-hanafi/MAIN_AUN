<?php
namespace Drupal\aun_system\Controller;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Component\Utility\UrlHelper;
use Drupal\node\Entity\Node;
use Drupal\Core\Url;

class print_test_message extends  ControllerBase{
  public function print_msg(){
 // print"ss";exit();
    //$content_type = \Drupal::request()->request->get('id');
    $content_nid = $_GET['id'];
    //print $content_type;exit();
    if($content_nid ==16){
      $content_type = "news";
    }else{
      $content_type = "team";
    }
    //$sql = "SELECT nid , title  FROM node_field_data node WHERE node.type ='$content_type'";
	$sql = "SELECT *  FROM int_news WHERE fac_ID = 2" ;
    //print $sql;exit();
    $database = \Drupal::database();
    $result = $database->query($sql);
    //print_r($result);exit();
    $i =1;
    while ($row_data = $result->fetchAssoc()) {
	  $news_title_en = $row_data['int_title_e'];
	  $news_body_en = $row_data['int_news_e'];
	  $news_title_ar = $row_data['int_title_a'];
	  $news_body_ar = $row_data['int_news_a'];
	  $news_date = $row_data['int_date'];
	  $node = Node::create([
		'type' => 'news',
		'langcode' => 'en',
		'created' => \Drupal::time()->getRequestTime(),
		'changed' => \Drupal::time()->getRequestTime(),
		'uid' => 1,
		'status' => 1,
		'title' => "$news_title_en",
		'body' => [
			'summary' => "$news_body_en",
			'value' => "$news_body_en",
			'format' => 'full_html',
		],
		'field_news_date' => [
			'value' => "$news_date",
		],
	  ]);
	  //Saving the node
	  $node->save();
	  $node_ar = $node->addTranslation('ar');
	  $node_ar->title = $news_title_ar;
	  $node_ar->body->value = "$news_body_ar";
	  $node_ar->body->format = 'full_html';
	  $node_ar->save();
	  //print $i." => ".$row_data['int_id']." <br> " ;		
	  print $i."- ";
	  $i++;
    }
  }
}

