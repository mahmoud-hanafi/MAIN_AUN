<?php 
namespace Drupal\gavias_blockbuilder\shortcodes;
if(!class_exists('gsc_carousel_content')):
   class gsc_carousel_content{

      public function render_form(){
         $fields = array(
            'type' => 'gsc_carousel_content',
            'title' => t('Carousel Content'),
            'size' => 3,
            'fields' => array(
               array(
                  'id'     => 'title',
                  'type'      => 'text',
                  'title'  => t('Title For Admin'),
                   'class'     => 'display-admin'
               ),
               array(
                  'id'        => 'bg_color',
                  'type'      => 'text',
                  'title'     => t('Background color'),
                  'desc'      => t('Background color fox box. e.g: #ccc, default background of theme')
               ),
               array(
                  'id'        => 'style',
                  'type'      => 'select',
                  'title'     => 'Style',
                  'options'   => array( 
                     'style-1'   => 'Style #1 (color white)', 
                     'style-2'   => 'Style #2 (color dark)'
                  ),
               ), 
               array(
                  'id'     => 'animate',
                  'type'      => 'select',
                  'title'  => ('Animation'),
                  'desc'  => t('Entrance animation for element'),
                  'options'   => gavias_blockbuilder_animate(),
               ),
               array(
                  'id'        => 'el_class',
                  'type'      => 'text',
                  'title'     => t('Extra class name'),
                  'desc'      => t('Style particular content element differently - add a class name and refer to it in custom CSS.'),
               ),   
            ),                                     
         );

         for($i=1; $i<=8; $i++){
            $fields['fields'][] = array(
               'id'     => "info_${i}",
               'type'   => 'info',
               'desc'   => "Information for item {$i}"
            );
            $fields['fields'][] = array(
               'id'        => "title_{$i}",
               'type'      => 'text',
               'title'     => t("Title {$i}")
            );
            $fields['fields'][] = array(
               'id'           => "content_{$i}",
               'type'         => 'textarea',
               'title'        => t("Content {$i}"),
               'desc'         => t('Shortcodes and HTML tags allowed.'),
               'shortcodes'   => 'on'
            );
            $fields['fields'][] = array(
               'id'        => "link_{$i}",
               'type'      => 'text',
               'title'     => t("Link {$i}")
            );
            $fields['fields'][] = array(
               'id'        => "image_{$i}",
               'type'      => 'upload',
               'title'     => t("Image {$i}")
            );
         }
         return $fields;
      }

      public function render_content( $item ) {
         print self::sc_carousel_content( $item['fields'] );
      }

      public static function sc_carousel_content( $attr, $content = null ){
         global $base_url;
         $default = array(
            'title'      => '',
            'bg_color'   => '',
            'style'      => 'style-1',
            'el_class'   => '',
            'animate'    => '',
         );

         for($i=1; $i<=10; $i++){
            $default["title_{$i}"] = '';
            $default["content_{$i}"] = '';
            $default["link_{$i}"] = '';
            $default["image_{$i}"] = '';
         }

         extract(shortcode_atts($default, $attr));

         $el_class .= ' ' . $style;
         $_id = gavias_blockbuilder_makeid();
         $bg_style = '';
         if($bg_color){
            $bg_style = "style=\"background-color: {$bg_color};\"";
         }
         if($animate){
            $el_class .= ' wow';
            $el_class .= ' ' . $animate;
         }
         ?>
         <?php ob_start() ?>
         
         <div class="gsc-carousel-content <?php print $el_class ?>"> 
            <div class="owl-carousel init-carousel-owl"data-items="1" data-items_lg="1" data-items_md="1" data-items_sm="1" data-items_xs="1" data-loop="1" data-speed="500" data-auto_play="0" data-auto_play_speed="0" data-auto_play_timeout="0" data-auto_play_hover="1" data-navigation="1" data-rewind_nav="0" data-pagination="0" data-mouse_drag="1" data-touch_drag="1">
               <?php for($i=1; $i<=8; $i++){ ?>
                  <?php 
                     $title = "title_{$i}";
                     $content = "content_{$i}";
                     $link = "link_{$i}";
                     $image = "image_{$i}";
                  ?>
                  <?php if($$title){ ?>
                     <div class="item">
                        <div class="item-content">
                           <?php if($$image){?>
                             <div class="bg-full" style="background-image:url('<?php print ($base_url . $$image) ?>') "></div>
                           <?php }  ?>
                           <div class="content-box" <?php print $bg_style ?>>
                              <div class="content-inner">
                                 <?php if($$title){ ?>
                                    <div class="title"><a href="<?php print $$link ?>"><?php print $$title ?></a></div>
                                 <?php } ?>
                                 <?php if($$content){ print '<div class="desc">' . $$content . '</div>'; } ?>
                                 <?php if($$link){ ?>
                                    <div class="action margin-top-20"><a class="btn btn-theme" href="<?php print $$link ?>"><?php print t('Read more') ?></a></div>
                                 <?php } ?>
                              </div>
                           </div>
                        </div>
                     </div>
                  <?php } ?>    
               <?php } ?>
            </div>   
         </div>  

         <?php return ob_get_clean();
      }

      public function load_shortcode(){
         add_shortcode( 'sc_carousel_content', array($this, 'sc_carousel_content') );
      }
   }
 endif;  



