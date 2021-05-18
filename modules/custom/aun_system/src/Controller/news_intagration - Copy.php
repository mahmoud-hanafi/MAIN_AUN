<?php
namespace Drupal\aun_system\Controller;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Component\Utility\UrlHelper;
use Drupal\node\Entity\Node;
use Drupal\Core\Url;

class news_intagration extends  ControllerBase{
  public function handle_news_intagration(){
	global $base_url;
	$sql = "SELECT * FROM `int_news` WHERE `fac_ID` = 02 ORDER BY int_id DESC LIMIT 0,3" ;
    $database = \Drupal::database();
    $result = $database->query($sql);
    $i =1;
    while ($row_data = $result->fetchAssoc()) {
	print $i." s- ";
	  $news_title_en = $row_data['int_title_e'];
	  if(empty($news_title_en)){
	    $news_title_en = $row_data['int_title_a'];
	  }
	  $news_body_en = $row_data['int_news_e'];
	  if(empty($news_body_en)){
	    $news_body_en = $row_data['int_news_a'];
	  }
	  $news_sumery_en = strip_tags($news_body_en);
	  $news_body_en = strip_tags($news_body_en);
	  
	  $old_file_path = $row_data['icon'];
	  $old_file_path = explode('/',$old_file_path);
	  $img_name = end($old_file_path);
	  $filePath = "sites/default/files/$img_name";
      $file = array(
        'uri'           => $filePath,
        'status'        => 1,
        'display'       => 1,
      );

	  $file2 = entity_create('file', $file);
	  $file3 = file_copy($file2, 'public://news/');
	 
	  $news_title_ar = $row_data['int_title_a'];	
	  $news_body_ar = $row_data['int_news_a'];
	  $news_sumery_ar = strip_tags($news_body_ar);
	  $news_date = $row_data['int_date'];
	  $node = array();
	  $node = Node::create([
		'type' => 'news',
		'langcode' => 'en',
		'created' => \Drupal::time()->getRequestTime(),
		'changed' => \Drupal::time()->getRequestTime(),
		'uid' => 1,
		'status' => 1,
		'title' => "$news_title_en",
		'body' => [
			'summary' => "$news_sumery_en",
			'value' => "$news_body_en",
			'format' => 'full_html',
		],
		'field_news_date' => [
			'value' => "$news_date",
		],
		'field_news_image' => [
			'target_id' => $file3->id(),
			'alt' => "$news_title_en",
			'title' => "$news_title_en",
		],
	  ]);
	  //Saving the node
	  $node->save();
	  
	  $node_ar = $node->addTranslation('ar');
	  $node_ar->title = $news_title_ar;
	  $node_ar->body->value = "$news_body_ar";
	  //$node_ar->body->summary = "$news_sumery_ar";
	  $node_ar->body->format = 'full_html';
	  $node_ar->save();
	  
	  //print $i." => ".$row_data['int_id']." <br> " ;		
	  //print $i."- ";
	  $i++;
    }
	exit();
  }
}

