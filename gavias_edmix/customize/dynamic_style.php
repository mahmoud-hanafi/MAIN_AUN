<style class="customize">
<?php
    $customize = (array)json_decode($json, true);
    if($customize):
?>

    <?php //================= Font Body Typography ====================== ?>
    <?php if(isset($customize['font_family_primary'])  && $customize['font_family_primary'] != '---'){ ?>
        body,
        .event-full .event-info, .block.block-blocktabs .ui-widget, .block.block-blocktabs .ui-tabs-nav > li > a, .gva-mega-menu .block-blocktabs .ui-widget, .gva-mega-menu .block-blocktabs .ui-tabs-nav > li > a,
        .gva-googlemap .gm-style-iw div .marker .info
        {
            font-family: <?php echo $customize['font_family_primary'] ?>!important;
        }
    <?php } ?> 

    <?php if(isset($customize['font_family_second'])  && $customize['font_family_second'] != '---'){ ?>
        h1, h2, h3, h4, h5, h6,
        .h1, .h2, .h3, .h4, .h5, .h6,
        ul.menu > li, .topbar .topbar-right ul.gva_topbar_menu > li a, .quick-cart .cart-block-contents-links a, .gavias_sliderlayer .slide-style-1, #gavias_slider_single .slide-style-1, .gavias_sliderlayer .slide-style-2, #gavias_slider_single .slide-style-2,
        .gavias_sliderlayer .slide-style-3, #gavias_slider_single .slide-style-3, .gavias_sliderlayer .btn-slide, .gavias_sliderlayer .btn-slide-white, #gavias_slider_single .btn-slide, #gavias_slider_single .btn-slide-white,
        .gavias_sliderlayer .btn-slide.inner, .gavias_sliderlayer .btn-slide a, .gavias_sliderlayer .btn-slide-white.inner, .gavias_sliderlayer .btn-slide-white a, #gavias_slider_single .btn-slide.inner, #gavias_slider_single .btn-slide a, #gavias_slider_single .btn-slide-white.inner, #gavias_slider_single .btn-slide-white a,
        .post-block .post-image .post-categories a, .post-block .post-title a, .post-slider.post-block .post-meta-wrap .post-title a, .post-slider.post-block .post-categories a, .portfolio-filter ul.nav-tabs > li > a, .portfolio-v1 .content-inner .title a, .team-teaser-1 .team-name, .team-teaser-2 .team-name, .testimonial-node-v1 .testimonial-content .info .right .title a,
        .testimonial-node-v2 .content-bottom .title, .course-block.featured-course .right .course-bottom .bottom-right .course-apply, .course-block.featured-course .right .course-features .field__label, .course-block.featured-course-2 .right .featured-label, .course-block.featured-course-2 .right .teacher a, .course-block.featured-course-2 .right .course-bottom .bottom-right .course-apply,
        .views-exposed-form .form-item select, .views-exposed-form .form-item input, .views-exposed-form .form-actions select, .views-exposed-form .form-actions input, .views-exposed-form .form-actions input, .categories-course-list .view-content-wrap .item a, .view-courses-featured-2 .carousel-nav .content-inner .info .right, .single-course .add-to-cart-content-inner .field--name-price, .single-course .add-to-cart-content-inner .user-registered,
        .single-course .block-info .block-info-title, .single-course .course-meta .meta-item .content .val, .single-course .course-meta .meta-item .content .val a, .single-course .course-features .field__label, .single-course .course-features .field__items .field__item, .lesson-block .lesson-content .lesson-title, .lesson-single .lessons .back-to-course, .event-block .event-content .event-info .title, .event-block .event-content .event-info .address,
        .event-block-list .event-date span.day, .event-block-list .event-title a, .nav-tabs > li > a, .btn, .btn-white, .btn-theme, input.js-form-submit, a.button-action, .portfolio-filter ul.nav-tabs > li > a, .pricing-table .content-inner .plan-name .title, .user-form .form-item:not(.js-form-type-checkbox) label, .user-form details summary, .sidebar .block-menu ul li a, .navigation .gva_menu > li > a, .navigation .gva_menu .sub-menu > li > a, .category-list .item-list ul li a,
        .widget.gsc-heading.style-default .title, .widget.gsc-heading.style-default .sub-title, .widget.gsc-icon-box .highlight_content .title, .widget.gsc-video-box .video-content .video-title, .widget.milestone-block .milestone-number, .widget.milestone-block .milestone-text, .gsc-image-content .action a, .gsc-tab-views.style-2 .list-links-tabs .nav-tabs > li a, .gsc-carousel-content.style-1 .content-box .content-inner .title, .gsc-carousel-content.style-2 .content-box .content-inner .title
        {
            font-family: <?php echo $customize['font_family_second'] ?>!important;
        }
    <?php } ?> 

    <?php if(isset($customize['font_body_size'])  && $customize['font_body_size']){ ?>
        body{
            font-size: <?php echo ($customize['font_body_size'] . 'px'); ?>;
        }
    <?php } ?>    

    <?php if(isset($customize['font_body_weight'])  && $customize['font_body_weight']){ ?>
        body{
            font-weight: <?php echo $customize['font_body_weight'] ?>;
        }
    <?php } ?>    

    <?php //================= Body ================== ?>

    <?php if(isset($customize['body_bg_image'])  && $customize['body_bg_image']){ ?>
        body{
            background-image:url('<?php echo drupal_get_path('theme', 'gavias_edmix') .'/images/patterns/'. $customize['body_bg_image']; ?>');
        }
    <?php } ?> 
    <?php if(isset($customize['body_bg_color'])  && $customize['body_bg_color']){ ?>
        body{
            background-color: <?php echo $customize['body_bg_color'] ?>!important;
        }
    <?php } ?> 
    <?php if(isset($customize['body_bg_position'])  && $customize['body_bg_position']){ ?>
        body{
            background-position:<?php echo $customize['body_bg_position'] ?>;
        }
    <?php } ?> 
    <?php if(isset($customize['body_bg_repeat'])  && $customize['body_bg_repeat']){ ?>
        body{
            background-repeat: <?php echo $customize['body_bg_repeat'] ?>;
        }
    <?php } ?> 

    <?php //================= Body page ===================== ?>
    <?php if(isset($customize['text_color'])  && $customize['text_color']){ ?>
        body .body-page{
            color: <?php echo $customize['text_color'] ?>;
        }
    <?php } ?>

    <?php if(isset($customize['link_color'])  && $customize['link_color']){ ?>
        body .body-page a{
            color: <?php echo $customize['link_color'] ?>!important;
        }
    <?php } ?>

    <?php if(isset($customize['link_hover_color'])  && $customize['link_hover_color']){ ?>
        body .body-page a:hover{
            color: <?php echo $customize['link_hover_color'] ?>!important;
        }
    <?php } ?>

    <?php //===================Header=================== ?>
    <?php if(isset($customize['header_bg'])  && $customize['header_bg']){ ?>
        header .header-main{
            background: <?php echo $customize['header_bg'] ?>!important;
        }
    <?php } ?>

    <?php if(isset($customize['header_color_link'])  && $customize['header_color_link']){ ?>
        header .header-main a{
            color: <?php echo $customize['header_color_link'] ?>!important;
        }
    <?php } ?>

    <?php if(isset($customize['header_color_link_hover'])  && $customize['header_color_link_hover']){ ?>
        header .header-main a:hover{
            color: <?php echo $customize['header_color_link_hover'] ?>!important;
        }
    <?php } ?>

   <?php //===================Menu=================== ?>
    <?php if(isset($customize['menu_bg']) && $customize['menu_bg']){ ?>
        .main-menu, ul.gva_menu{
            background: <?php echo $customize['menu_bg'] ?>!important;
        }
    <?php } ?> 

    <?php if(isset($customize['menu_color_link']) && $customize['menu_color_link']){ ?>
        .main-menu ul.gva_menu > li > a{
            color: <?php echo $customize['menu_color_link'] ?>!important;
        }
    <?php } ?> 

    <?php if(isset($customize['menu_color_link_hover']) && $customize['menu_color_link_hover']){ ?>
        .main-menu ul.gva_menu > li > a:hover{
            color: <?php echo $customize['menu_color_link_hover'] ?>!important;
        }
    <?php } ?> 

    <?php if(isset($customize['submenu_background']) && $customize['submenu_background']){ ?>
        .main-menu .sub-menu{
            background: <?php echo $customize['submenu_background'] ?>!important;
            color: <?php echo $customize['submenu_color'] ?>!important;
        }
    <?php } ?> 

    <?php if(isset($customize['submenu_color']) && $customize['submenu_color']){ ?>
        .main-menu .sub-menu{
            color: <?php echo $customize['submenu_color'] ?>!important;
        }
    <?php } ?> 

    <?php if(isset($customize['submenu_color_link']) && $customize['submenu_color_link']){ ?>
        .main-menu .sub-menu a{
            color: <?php echo $customize['submenu_color_link'] ?>!important;
        }
    <?php } ?> 

    <?php if(isset($customize['submenu_color_link_hover']) && $customize['submenu_color_link_hover']){ ?>
        .main-menu .sub-menu a:hover{
            color: <?php echo $customize['submenu_color_link_hover'] ?>!important;
        }
    <?php } ?> 

    <?php //===================Footer=================== ?>
    <?php if(isset($customize['footer_bg']) && $customize['footer_bg'] ){ ?>
        #footer .footer-center{
            background: <?php echo $customize['footer_bg'] ?>!important;
        }
    <?php } ?>

     <?php if(isset($customize['footer_color'])  && $customize['footer_color']){ ?>
        #footer .footer-center{
            color: <?php echo $customize['footer_color'] ?> !important;
        }
    <?php } ?>

    <?php if(isset($customize['footer_color_link'])  && $customize['footer_color_link']){ ?>
        #footer .footer-center ul.menu > li a::after, .footer a{
            color: <?php echo $customize['footer_color_link'] ?>!important;
        }
    <?php } ?>    

    <?php if(isset($customize['footer_color_link_hover'])  && $customize['footer_color_link_hover']){ ?>
        #footer .footer-center a:hover{
            color: <?php echo $customize['footer_color_link_hover'] ?> !important;
        }
    <?php } ?>    

    <?php //===================Copyright======================= ?>
    <?php if(isset($customize['copyright_bg'])  && $customize['copyright_bg']){ ?>
        .copyright{
            background: <?php echo $customize['copyright_bg'] ?> !important;
        }
    <?php } ?>

     <?php if(isset($customize['copyright_color'])  && $customize['copyright_color']){ ?>
        .copyright{
            color: <?php echo $customize['copyright_color'] ?> !important;
        }
    <?php } ?>

    <?php if(isset($customize['copyright_color_link'])  && $customize['copyright_color_link']){ ?>
        .copyright a{
            color: $customize['copyright_color_link'] ?>!important;
        }
    <?php } ?>    

    <?php if(isset($customize['copyright_color_link_hover'])  && $customize['copyright_color_link_hover']){ ?>
        .copyright a:hover{
            color: <?php echo $customize['copyright_color_link_hover'] ?> !important;
        }
    <?php } ?>    
<?php endif; ?>    
</style>
