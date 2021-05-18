<?php
namespace Drupal\aun_system\Controller;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Component\Utility\UrlHelper;
use Drupal\node\Entity\Node;
use Drupal\Core\Url;

class events_intagration extends  ControllerBase{
  public function handle_events_intagration(){
	global $base_url;
	$sql = "SELECT * FROM `local_conf` where `CON_ID` > 243  ORDER BY `local_conf`.`CON_ID` DESC" ;
    $database = \Drupal::database();
    $result = $database->query($sql);
	//print_r($result);exit();
    $i =1;
    while ($row_data = $result->fetchAssoc()) {
	//	print_r($row_data);exit();
	$event_id =  $row_data['CON_ID'];
	print $i." s- ".$event_id."/";
	
	  $event_title_en = $row_data['CON_eTitle'];
	  $event_title_ar = $row_data['CON_aTitle'];	
	  $event_start_date = $row_data['CONF_Start']; 
	  $event_end_date = $row_data['CONF_End'];
      $event_address_en = "Assiut University";	  
	  $event_address_ar = "جامعة اسيوط";	  
	  $event_time = "9Am - 2PM";
	  $old_file_path = $row_data['CONF_Logo'];	  
	  $old_file_path = explode('/',$old_file_path);
	  $img_name = end($old_file_path);
	  $img_name = "events.jpg";
	  $filePath = "sites/default/files/$img_name";
	  $file = array(
		'uri'           => $filePath,
		'status'        => 1,
		'display'       => 1,
	  );
	  $file2 = entity_create('file', $file);
	  $file3 = file_copy($file2, 'public://events/');
	  /*
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
	  */
	  if(empty($event_title_en)){
		$event_title_ar = $row_data['CON_aTitle'];	
		$node = array();
	    $node = Node::create([
		  'type' => 'event',
		  'langcode' => 'ar',
		  'created' => \Drupal::time()->getRequestTime(),
		  'changed' => \Drupal::time()->getRequestTime(),
		  'uid' => 1,
		  'status' => 1,
		  'title' => "$event_title_ar",
		  'field_event_start' => [
			'value' => "$event_start_date",
		  ],
		  'field_event_end' => [
			'value' => "$event_end_date",
		  ],
		  'field_event_address' => [
			'value' => "$event_address_ar",
		  ],
		  'field_event_time' => [
			'value' => "$event_time",
		  ],
	    ]);
	    $node->save();
	  }else{  
	    $node = array();
	    $node = Node::create([
		  'type' => 'event',
		  'langcode' => 'en',
		  'created' => \Drupal::time()->getRequestTime(),
		  'changed' => \Drupal::time()->getRequestTime(),
		  'uid' => 1,
		  'status' => 1,
		  'title' => "$event_title_en",
		  'field_event_start' => [
			'value' => "$event_start_date",
		  ],
		  'field_event_end' => [
			'value' => "$event_end_date",
		  ],
		  'field_event_address' => [
			'value' => "$event_address_en",
		  ],
		  'field_event_time' => [
			'value' => "$event_time",
		  ],
	    ]);
		//print_r($node);exit();
		$node->save();
		if(!empty($event_title_ar) && !empty($event_title_en)){
	    $node_ar = $node->addTranslation('ar');
	    $node_ar->title = $event_title_ar;
	    $node_ar->field_event_address->value = "$event_address_ar";
		$node_ar->field_event_time->value = "$event_time";
		//$node_ar->field_event_image->target_id = $file3->id();
	    $node_ar->save();
		}
	  }
	  $i++;
    }
	exit();
  }
}

