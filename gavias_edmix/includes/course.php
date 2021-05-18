<?php
function gavias_edmix_check_registered_course($uid, $course_id){
  if($uid == 0) return false;
  $results = db_select('{commerce_order_item}', 'oi');
  $results->leftJoin('{commerce_order}', 'o', 'oi.order_id = o.order_id');
  $results->leftJoin('{commerce_product_variation_field_data}', 'v', 'oi.purchased_entity = v.variation_id');
  $results->fields('v', array('product_id', 'type'));
  $results->fields('o', array('state', 'uid'));
  $results->fields('oi', array('unit_price__number', 'total_price__number'));
  $results->condition(
    db_or()
      ->condition('o.state', 'payment_received', '=')
      ->condition('o.state', 'completed', '=')
  );
  $results->condition('v.product_id', $course_id, '=');
  $results->condition('o.uid', $uid, '=');
  $results->condition('v.type', 'course', '=');
  $orders = $results->execute()->fetchAll(PDO::FETCH_ASSOC);
  if(count($orders) > 0){
    return true;
  }
  return false;
}

function gavias_edmix_preprocess_course(&$vars){
   $vars['#cache']['max-age'] = 0;
   $product_entity = $vars['product_entity'];
   $product = $vars['product'];
  
   $id = $product_entity->id();
   $video_link = '';

  if($product_entity->hasField('field_course_video')){
    $video_link = $product_entity->get('field_course_video')->getValue();
    if(isset($video_link[0]['value'])){
      $video_link = $video_link[0]['value'];
    }
  }

  if($product_entity->hasField('title')){
    $title = $product_entity->get('title')->getValue();
    if(isset($title[0]['value'])){
      $title = $title[0]['value'];
    }
  }

  $current_user = \Drupal::currentUser();
  $uid = $current_user->id();
  if(gavias_edmix_check_registered_course($uid, $id)){
    $vars['product']['variations'] = '<div class="user-registered">' . t('Registered') . '</div>';
  }

  $vars['video_link'] = $video_link;
  $vars['title'] = $title;
  return $vars;
}

function gavias_edmix_preprocess_commerce_product__course(&$vars){
  $vars = gavias_edmix_preprocess_course($vars);
}
function gavias_edmix_preprocess_commerce_product__course__featured_2(&$vars){
  $vars = gavias_edmix_preprocess_course($vars);
}
function gavias_edmix_preprocess_commerce_product__course__featured(&$vars){
  $vars = gavias_edmix_preprocess_course($vars);
}
function gavias_edmix_preprocess_commerce_product__course__teaser(&$vars){
  $vars = gavias_edmix_preprocess_course($vars);
}
function gavias_edmix_preprocess_commerce_product__course__teaser_2(&$vars){
  $vars = gavias_edmix_preprocess_course($vars);
}

function gavias_edmix_preprocess_node__lesson(&$variables) {
  $variables['#attached']['library'][] = 'gavias_edmix/gavias-lesson-video';
  $lesson_access = 'registered';
  $lesson_content = '';
  $lesson_icon = 'gv-icon-49';
  $user = \Drupal::currentUser();
  $role = $user->getRoles();
  $course_id = 0;
  $role_admin = '';
  if($node = $variables['node']){
    if($node->hasField('field_lesson_access')){
      $lesson_access = $node->field_lesson_access->value;
    }
    if($node->hasField('field_lecture_course')){
      $field_lecture_course = $node->get('field_lecture_course');
      if(isset($field_lecture_course[0]) && isset($field_lecture_course[0]->target_id)){
            $course_id = $field_lecture_course[0]->target_id;
         }
    }

    if(in_array("administrator", $role)){
      $lesson_icon = 'gv-icon-2';
      $role_admin = t(' Admin');
      if(isset($variables['content']['field_lesson_content'])){
        $lesson_content = $variables['content']['field_lesson_content'];
      }
    }else{
      if($lesson_access == 'registered'){
        if( $user->id() && gavias_edmix_check_registered_course($user->id(), $course_id) ){
          $lesson_icon = 'gv-icon-2';
          if(isset($variables['content']['field_lesson_content'])){
            $lesson_content = $variables['content']['field_lesson_content'];
          }    
        }else{
          $lesson_content = '<div class="alert alert-info fade in alert-dismissable">' . t('Please Registered to view this lesson. ') . '<a href="'. \Drupal::url('user.login') .'"><strong>' . t('Login Page') . '</strong></a></div>';
        }

      }elseif ($lesson_access == 'logged_in'){
        if (!$user->id()) {
          $lesson_content = '<div class="alert alert-info fade in alert-dismissable">' . t('Please Login to view this lesson. ') . '<a href="'. \Drupal::url('user.login') .'"><strong>' . t('Login Page') . '</strong></a></div>';
        }
        else {
          if(isset($variables['content']['field_lesson_content'])){
            $lesson_content = $variables['content']['field_lesson_content'];
          }
          $lesson_icon = 'gv-icon-2';
        }
      }elseif($lesson_access == 'public'){
        if(isset($variables['content']['field_lesson_content'])){
          $lesson_content = $variables['content']['field_lesson_content'];
        }
        $lesson_icon = 'gv-icon-2';
      }else{
        if( $user->id() && gavias_edmix_check_registered_course($user->id(), $course_id) ){
          $lesson_icon = 'gv-icon-2';
          if(isset($variables['content']['field_lesson_content'])){
            $lesson_content = $variables['content']['field_lesson_content'];
          }    
        }else{
          $lesson_content = '<div class="alert alert-info fade in alert-dismissable">' . t('Please register for course to view this lesson. ') . '<a href="'. base_path() .'/user"><strong>' . t('Login Page') . '</strong></a></div>';
        }
      }
    } 
  }
  $variables['course_id'] =  $course_id;
  $variables['lesson_content'] = $lesson_content;
  $variables['lesson_icon'] = $lesson_icon;
  $variables['role_admin'] = $role_admin;
}