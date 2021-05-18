<?php

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Component\Utility\UrlHelper;
use Drupal\node\Entity\Node;
use Drupal\Core\Url;
use Drupal\file\Entity\File;
use Drupal\media\Entity\Media;
use Drupal\field\Entity\FieldConfig;



function aun_system_form_alter(&$form, \Drupal\Core\Form\FormStateInterface $form_state, $form_id) {
//  \Drupal::service('module_installer')->uninstall(['sitemap']);
//$installer = \Drupal::service('module_installer');
//$installer->install(['sitemap']);
// xmlsitemap simple_sitemap/
//$installer->uninstall(['sitemap']);
//$installer->uninstall(['xmlsitemap']);
//$installer->uninstall(['simple_sitemap']);
//\Drupal::service('router.builder')->rebuild();
//drupal_flush_all_caches();
//$installer = $container->get('module_installer');
//$installer = \Drupal::service('module_installer');

//$installer->uninstall(['sitemap']);
//drupal_flush_all_caches();

//print $form_id;exit();
  if($form_id == 'node_student_form'){
    //drupal_set_title('اضافة طالب');
    //print_r($form);exit();
    $form['#title'] = t('اضافة طالب');
    $form['title']['#value'] = t('اضافة طالب');
    $form['title']['#value'] = '12345';
    $form['field_student_number']['widget'][0]['value']['#default_value'] = '112'; // for textfield.
    $form['field_student_number']['widget'][0]['value']['#required'] = 1;
    //$form['field_student_number']['#access'] = FALSE;
  }
}

function _return_category_value(){
  $sql = "SELECT node.nid FROM node_field_data node WHERE node.type ='student'";
  //print $sql;exit();
  $database = \Drupal::database();
  $result = $database->query($sql);
  $i =1;
  while ($row_data = $result->fetchAssoc()) {
    $student_nid = $row_data['nid'];
  }

}

function _handle_home_news_block($language){
  $sql = "SELECT node.nid FROM node_field_data node WHERE node.type ='news' and  langcode ='$language'  order by nid desc limit 0,3";
  //print $sql;exit();
  $database = \Drupal::database();
  $result = $database->query($sql);
  $i =1;
  $side_tabs = "";
  $content_tab= "";
  $i=0;
  while ($row_data = $result->fetchAssoc()) {
    $news_nid = $row_data['nid'];
    //print $news_nid;exit();
    $node = \Drupal::entityTypeManager()->getStorage('node')->load($news_nid);
    $news_title = $node->getTitle();
    $news_img = "<img src='/main/sites/default/files/inline-images/prof-tark.png' />";
    $news_date= $node->get('field_news_date')->value;
    $news_text = $node->get('body')->value;
    $uri = $node->get('field_news_image')->entity->uri->value;
    $img_url = file_create_url($uri);
    if(empty($uri)){ // to get default image 
	$field_info = FieldConfig::loadByName('node', 'news', 'field_news_image');
	$image_uuid = $field_info->getSetting('default_image')['uuid'];
	$image = Drupal::service('entity.repository')->loadEntityByUuid('file', $image_uuid);
	$image_uri = $image->getFileUri();
        $img_url   = file_create_url($image_uri);
    }
    if($i < 1){ $class='active';}else{$class="";}
    $side_tabs .= "
       <li class='$class'>
                <a data-toggle='tab' href='#$news_nid'>
                    <div class='side_tab'>
                        <div class='tab-img'><img src='$img_url' /></div>
                    </div>
                    <div class='tab-content'>
                        <p class='admin-name'>$news_title</p>
                        <p class='admin-position'> <i class='fa fa-clock-o'></i> $news_date</p>
                    </div>
                </a>
       </li>

    ";

//    $content_tab .="<div class='tab-pane $class' id='".$news_nid."'>$news_nid</div>";
    $content_tab .="
            <div class='tab-pane $class' id='$news_nid'>
                <div class='tab-content-right'>
                    <div class='user-data'>
                        <div class='user-img'><img src='$img_url' /></div>
                    </div>
                    <div class='cv-class'><a href=''> $news_title</a></div>
                    <p class='admin-position'> <i class='fa fa-clock-o'></i> $news_date</p>

                    <div class='other-user-content'>
                       $news_text
                    </div>
                </div>
            </div>
    ";
    $i++;
  }
 

  $block_content ='
	<div class="col-sm-12">
	    <div class="col-xs-5">
        	<ul class="nav nav-tabs tabs-left sideways">
                   '.$side_tabs.' 
	        </ul>
	    </div>
	    <div class="col-xs-7">
        	<div class="tab-content">
            		'.$content_tab.'      
	        </div>
	    </div>
	</div>
  ';

  return $block_content;
}



?>
