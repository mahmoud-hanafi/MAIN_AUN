<?php 
namespace Drupal\gavias_blockbuilder\shortcodes;
if(!class_exists('gsc_services_grid')):
   class gsc_services_grid{

      public function render_form(){
         $fields = array(
            'type' => 'gsc_services_grid',
            'title' => t('Services Grid'),
            'size' => 3,
            'fields' => array(
               array(
                  'id'     => 'title',
                  'type'      => 'text',
                  'title'  => t('Title For Admin'),
               ),
               array(
                  'id'     => 'more_link',
                  'type'      => 'text',
                  'title'  => t('Link view more'),
               ),
               array(
                  'id'     => 'more_text',
                  'type'      => 'text',
                  'title'  => t('Text Link view more'),
               ),
               array(
                  'id'     => 'col_lg',
                  'type'   => 'select',
                  'title'  => t('Columns for Large screen'),
                  'options' => array(1=>1, 2=>2, 3=>3, 4=>4, 5=>5, 6=>6, 7=>7, 8=>8, 9=>9, 10=>10, 11=>11, 12=>12),
                  'std'    => 4
               ),
               array(
                  'id'     => 'col_md',
                  'type'   => 'select',
                  'title'  => t('Columns for Medium screen'),
                  'options' => array(1=>1, 2=>2, 3=>3, 4=>4, 5=>5, 6=>6, 7=>7, 8=>8, 9=>9, 10=>10, 11=>11, 12=>12),
                  'std'    => 4
               ),
               array(
                  'id'     => 'col_sm',
                  'type'      => 'select',
                  'title'  => t('Columns for Small screen'),
                  'options' => array(1=>1, 2=>2, 3=>3, 4=>4, 5=>5, 6=>6, 7=>7, 8=>8, 9=>9, 10=>10, 11=>11, 12=>12),
                  'std'    => 2
               ),
               array(
                  'id'     => 'col_xs',
                  'type'      => 'select',
                  'title'  => t('Columns for Extra Small screen'),
                  'options' => array(1=>1, 2=>2, 3=>3, 4=>4, 5=>5, 6=>6, 7=>7, 8=>8, 9=>9, 10=>10, 11=>11, 12=>12),
                  'std'    => 2
               ),
               array(
                  'id'     => 'animate',
                  'type'      => 'select',
                  'title'  => ('Animation'),
                  'desc'  => t('Entrance animation for element'),
                  'options'   => gavias_blockbuilder_animate_aos(),
               ),
               array(
                  'id'        => 'el_class',
                  'type'      => 'text',
                  'title'     => t('Extra class name'),
                  'desc'      => t('Style particular content element differently - add a class name and refer to it in custom CSS.'),
               ),   
            ),                                     
         );

         for($i=1; $i<=10; $i++){
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
               'id'           => "icon_{$i}",
               'type'         => 'text',
               'title'        => t("Icon {$i}"),
            );
            $fields['fields'][] = array(
               'id'           => "color_{$i}",
               'type'         => 'text',
               'title'        => t("Background Item {$i}"),
            );
            $fields['fields'][] = array(
               'id'        => "link_{$i}",
               'type'      => 'text',
               'title'     => t("Link {$i}")
            );
         }
         return $fields;
      }

      public function render_content( $item ) {
         print self::sc_services_grid( $item['fields'] );
      }

      public static function sc_services_grid( $attr, $content = null ){
         global $base_url;
         $default = array(
            'title'      => '',
            'more_link'  => '',
            'more_text'  => 'View all services',
            'col_lg'     => '4',
            'col_md'     => '4',
            'col_sm'     => '2',
            'col_xs'     => '2',
            'el_class'   => '',
            'animate'    => '',
         );

         for($i=1; $i<=10; $i++){
            $default["title_{$i}"] = '';
            $default["icon_{$i}"] = '';
            $default["link_{$i}"] = '';
            $default["color_{$i}"] = '';
         }

         extract(shortcode_atts($default, $attr));

         $_id = gavias_blockbuilder_makeid();
         
         ?>
         <?php ob_start() ?>
         <div class="gsc-service-grid <?php echo $el_class ?>" <?php print gavias_print_animate_aos($animate) ?>> 
            <div class="lg-block-grid-<?php echo $col_lg ?> md-block-grid-<?php echo $col_md ?> sm-block-grid-<?php echo $col_sm ?> xs-block-grid-<?php echo $col_xs ?>">
               <?php for($i=1; $i<=10; $i++){ ?>
                  <?php 
                     $title = "title_{$i}";
                     $icon = "icon_{$i}";
                     $link = "link_{$i}";
                     $color = "color_{$i}";
                     $style = '';
                     if($$color) $style = ' style="background-color: '.$$color.';"';
                  ?>
                  <?php if($$title){ ?>
                     <div class="item-columns item<?php print ($color?' has-bg-color': ''); ?>"><div class="content-inner"<?php print $style ?>>
                     <?php if($$icon){ ?><div class="icon"><a href="<?php print $$link ?>"><i class="<?php print $$icon ?>"></i></a></div><?php } ?>         
                     <?php if($$title){ ?><div class="title"><a href="<?php print $$link ?>"><?php print $$title ?></a></div><?php } ?>
                     </div></div>
                  <?php } ?>    
               <?php } ?>
            </div> 
            <?php if($more_link){ ?>
               <div class="read-more"><a class="btn-theme" href="<?php print $more_link ?>"><?php print $more_text ?></a></div>
            <?php } ?>   
         </div>   

         <?php return ob_get_clean();
      }

      public function load_shortcode(){
         add_shortcode( 'services_grid', array($this, 'sc_services_grid') );
      }
   }
 endif;  



