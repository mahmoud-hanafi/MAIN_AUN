<?php
function gavias_edmix_preprocess_views_view_grid(&$variables) {
   $view = $variables['view'];
   $rows = $variables['rows'];
   $style = $view->style_plugin;
   $options = $style->options;
   $variables['gva_masonry']['class'] = '';
   $variables['gva_masonry']['class_item'] = '';
   if(strpos($options['row_class_custom'] , 'masonry') || $options['row_class_custom'] == 'masonry' ){
      $variables['gva_masonry']['class'] = 'post-masonry-style row';
      $variables['gva_masonry']['class_item'] = 'item-masory';
   }
   $variables['attributes']['class'][]="gva-view";
   if(is_numeric(strpos($view->current_display, 'page'))){
      $variables['attributes']['class'][] = "view-page";
   }
}

global $node_index;
function gavias_edmix_preprocess_views_view(&$variables) {
   global $node_index;
   $node_index = 0;
}