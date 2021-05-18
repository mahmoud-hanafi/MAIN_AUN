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
	//$sql = "SELECT * FROM `int_news_main` inner join int_pics using(int_id) " ; 
	//$sql = "SELECT * FROM `int_news_main` inner join int_pics using(int_id) WHERE `fac_ID` = 00 order by int_id asc " ;
	$sql = "SELECT * FROM `int_news_main` where `int_id` > 21474907149 ";
	//print $sql;exit();
    $database = \Drupal::database();
    $result = $database->query($sql);
    $i =1;
	//print $sql;exit();
    while ($row_data = $result->fetchAssoc()) {
	  $int_news = $row_data['int_id'];
	  print $i." s- $int_news  /  ";
	  $news_title_en = $row_data['int_title_e'];
	  //$old_file_path = $row_data['int_pic'];
	  //$old_file_path = explode('/',$old_file_path);
	  /*
	  $img_name = end($old_file_path);
	  $filePath = "sites/default/files/uploaded_imgs/$img_name";
	  if(strpos("uploaded_imgs",$old_file_path !== false )){
		  $filePath = "sites/default/files/$img_name";
	  }
	  $file = array(
		'uri'           => $filePath,
		'status'        => 1,
		'display'       => 1,
	  );
	  $file2 = entity_create('file', $file);
	  $file3 = file_copy($file2, 'public://news/');
	  */
	  
	  //$img_name = end($old_file_path);
	  //print $img_name;exit();
	  //$file_image="E:/xampp/htdocs/AUN/AUN/Uploadimage/$img_name";             
      //$file_content = file_get_contents($file_image);
	  //$directory = 'public://news/';
      //file_prepare_directory($directory, FILE_CREATE_DIRECTORY);
      //$file_image = file_save_data($file_content, $directory . basename($file_image),FILE_EXISTS_REPLACE);
	  $news_body_en = $row_data['int_news_e'];
	  $news_title_ar = $row_data['int_title_a'];	
	  $news_body_ar = $row_data['int_news_a'];
	  $news_title_fr = $row_data['int_title_f'];
	  $news_body_fr = $row_data['int_news_f'];
	  $news_title_de = $row_data['int_title_SP'];
	  $news_body_de = $row_data['int_news_SP'];
	  $grad_en = "alumni";
	  $grad_ar = "الخريجين";
	  $grad_fr = "anciens";
	  $grad_de = "alumni";
	  if((strpos($news_body_ar,$grad_ar) !== false)){
        $news_cat = 0;
		print $int_news." dd ";
	  }
	  if((strpos($news_title_ar,$grad_ar) !== false)){
        $news_cat = 0;
		print $int_news." dd ";
	  }
	  if(empty($news_title_en)){
		$news_title_ar = $row_data['int_title_a'];	
	    $news_body_ar = $row_data['int_news_a'];
	    $news_sumery_ar = strip_tags($news_body_ar);
		if(empty($news_body_ar)){
			$news_body_ar = $news_title_ar;
		}
	    $news_date = $row_data['int_date']; 
		$node = array();
	    $node = Node::create([
		  'type' => 'news',
		  'langcode' => 'ar',
		  'created' => \Drupal::time()->getRequestTime(),
		  'changed' => \Drupal::time()->getRequestTime(),
		  'uid' => 1,
		  'status' => 1,
		  'title' => "$news_title_ar",
		  'body' => [
			'summary' => "$news_sumery_ar",
			'value' => "$news_body_ar",
			'format' => 'full_html',
		  ],
		  'field_news_date' => [
			'value' => "$news_date",
		  ],
		  'field_news_category' => [
			'value' => $news_cat,
		  ],
		  //'field_news_image' => array('target_id' =>$file_id),
	    ]);
		//print_r($node);exit();
	    $node->save();
	  }else{  
		$news_body_en = $row_data['int_news_e'];
		$news_sumery_en = strip_tags($news_body_en);
		$news_body_en = strip_tags($news_body_en);
	    $news_title_ar = $row_data['int_title_a'];	
	    $news_body_ar = $row_data['int_news_a'];
	    $news_sumery_ar = strip_tags($news_body_ar);
		if(empty($news_body_en)){
			$news_body_en = $news_title_en;
		}
		if(empty($news_body_ar)){
			$news_body_ar = $news_title_ar;
		}
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
		  'field_news_category' => [
			'value' => $news_cat,
		  ],
		  //'field_news_image' => array('target_id' =>$file_id),
	    ]);
		$node->save();
		if(!empty($news_title_ar) && !empty($news_title_en)){
	    $node_ar = $node->addTranslation('ar');
		$node_ar->title = $news_title_ar;
		if(!empty($news_body_ar)){
		$node_ar->body->value = "$news_body_ar";
	    //$node_ar->body->summary = "$news_sumery_ar";
		$node_ar->body->format = 'full_html';
		}
		$node_ar->save();
		}
		//french content
		if(!empty($news_title_fr)){
		$node_fr = $node->addTranslation('fr');
		$node_fr->title = $news_title_fr;
		if(!empty($news_body_fr)){
	    $node_fr->body->value = "$news_body_fr";
		$node_fr->body->format = 'full_html';
		}
		$node_fr->save();
		}
		// german content
		if(!empty($news_title_de)){
		$node_de = $node->addTranslation('de');
		$node_de->title = $news_title_de;
		if(!empty($news_body_de)){
	    $node_de->body->value = "$news_body_de";
		$node_de->body->format = 'full_html';
		}
		$node_de->save();
		}
	  }
	  $i++;
    }
	exit();
  }
}

