<?php

/**
 * @file
 * Functions to support theming in the Bartik theme.
*/

use Drupal\Core\Template\Attribute;
use Drupal\block\Entity\Block;
use Drupal\node\Entity\Node;
  use Drupal\node\Entity\NodeType;



$theme_path = drupal_get_path('theme', 'gavias_edmix');
include_once $theme_path . '/includes/template.functions.php';
include_once $theme_path . '/includes/functions.php';
include_once $theme_path . '/includes/template.menu.php';
include_once $theme_path . '/includes/oembed.php';
include_once $theme_path . '/includes/override.php';
include_once $theme_path . '/includes/contact.php';
include_once $theme_path . '/customize/fonts.php';
include_once $theme_path . '/includes/course.php';

global $base_site_url;
global $base_url;
$language = \Drupal::languageManager()->getCurrentLanguage()->getId();
if($language != 'en'){
   $base_site_url = $base_url."/$language";
 //  global $base_site_url;
}


function gavias_edmix_preprocess_page(&$variables) {
  global $base_url;
  $theme_path = drupal_get_path('theme', 'gavias_edmix');
  $variables['sticky_menu'] = theme_get_setting('sticky_menu');
  $variables['footer_first_size'] = theme_get_setting('footer_first_size');
  $variables['footer_second_size'] = theme_get_setting('footer_second_size');
  $variables['footer_third_size'] = theme_get_setting('footer_third_size');
  $variables['footer_four_size'] = theme_get_setting('footer_four_size');

  $variables['preloader'] = theme_get_setting('preloader');

  $variables['theme_path'] = $base_url . '/' . $theme_path;

  //Header setting -----------
  $header = 'header';
  if(theme_get_setting('default_header')){
    $header = theme_get_setting('default_header');
  }

  if(isset($variables['gva_header']) && $variables['gva_header'] && $variables['gva_header']!='_none' ){
    $header = $variables['gva_header'];
  }

  if(file_exists($theme_path . '/templates/page/' . trim($header) . '.html.twig')){
    $variables['header_skin'] = $variables['directory'] . '/templates/page/' . trim($header) . '.html.twig';
  }else{
    $variables['header_skin'] = $variables['directory'] . '/templates/page/header.html.twig';
  }

  //Social setting -------------
  $custom_social_link = array();
  $custom_social_link['facebook']   = theme_get_setting('facebook');
  $custom_social_link['twitter']    = theme_get_setting('twitter');
  $custom_social_link['skype']      = theme_get_setting('skype');
  $custom_social_link['instagram']  = theme_get_setting('instagram');
  $custom_social_link['dribbble']   = theme_get_setting('dribbble');
  $custom_social_link['linkedin']   = theme_get_setting('linkedin');
  $custom_social_link['pinterest']  = theme_get_setting('pinterest');
  $custom_social_link['google']     = theme_get_setting('google');
  $custom_social_link['youtube']    = theme_get_setting('youtube');
  $custom_social_link['vimeo']      = theme_get_setting('vimeo');
  $custom_social_link['tumblr']     = theme_get_setting('tumblr');
  $variables['custom_social_link']  = $custom_social_link;

  $custom_account = '';
  $user = \Drupal::currentUser();
  if($user->id()){
    $custom_account = "Howdy, <a class=\"link-user\" href=\"{$base_url}/user/\"><span>{$user->getUsername()}</span></a>-<a href=\"{$base_url}/user/logout\">Logout</a>";
  }
  $variables['custom_account'] = $custom_account;
  $variables['login_link'] = \Drupal::url('user.login');
  $variables['register_link'] = \Drupal::url('user.register');
}

function gavias_edmix_preprocess_image(&$variables) {
  if (isset($variables['attributes']['width']) && isset($variables['attributes']['height'])) {
    unset($variables['attributes']['width']);
    unset($variables['attributes']['height']);
  }
}

/**
 * Implements hook_preprocess_HOOK() for HTML document templates.
 *
 * Adds body classes if certain regions have content.
 */
function gavias_edmix_preprocess_html(&$variables) {
  global $theme, $base_url;
  global $parent_root;

  $theme_path = drupal_get_path('theme', 'gavias_edmix');

  if(theme_get_setting('enable_panel') == '1' ){
    $current_url = $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"];
    $current_url = preg_replace('/([?&])display=[^&]+(&|$)/','$2',$current_url);
    if(strpos($current_url, '?')){
       $current_url .= '&';
    }
    else{
       $current_url .= '?';
    }
    $variables['current_url'] = $current_url;
  }

  $tmp_logo = theme_get_setting('logo');

  $variables['site_logo'] = $tmp_logo['url'];

  $variables['theme_path'] = $base_url . '/' . $theme_path;

  if(theme_get_setting('customize_css') ){
    $custom_style  = preg_replace( '!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', theme_get_setting('customize_css') );
    $custom_style = str_replace( array( "\r\n", "\r", "\n", "\t", '  ', '   ', '    ' ), '', $custom_style );
    $variables['customize_css'] =  $custom_style;
  }

  //--- Customize gaviasthemer ---
  $customize_styles = '';
  $json = '';

  ob_start();
  $json = \Drupal::config('gaviasthemer.settings')->get('gavias_customize');

  if(!$json){
    if(file_exists($theme_path . '/css/customize.json')){
      $json = file_get_contents($theme_path . '/css/customize.json');
    }
  }

  $variables['links_google_fonts'] = gavias_edmix_links_typography_font($json);
  require_once($theme_path . '/customize/dynamic_style.php');
  $customize_styles = ob_get_clean();
  $customize_styles  = preg_replace( '!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $customize_styles );
  $customize_styles = str_replace( array( "\r\n", "\r", "\n", "\t", '  ', '   ', '    ' ), '', $customize_styles );

  $variables['customize_styles'] = $customize_styles;

  //Form customize
  $user = \Drupal::currentUser();

  if(theme_get_setting('enable_customize') == 1 &&  \Drupal::moduleHandler()->moduleExists('gaviasthemer') && ($user->hasPermission('administer gavias_customize') || $user->hasPermission('administer gavias_customize preview')) ){
    $url_customize_save = \Drupal::url('gaviasthemer.admin.customize_save', array(), array('absolute' => TRUE));
    $variables['#attached']['drupalSettings']['gavias_customize']['save'] = $url_customize_save;
    $url_customize_preview = \Drupal::url('gaviasthemer.admin.customize_preview', array(), array('absolute' => TRUE));
    $variables['#attached']['drupalSettings']['gavias_customize']['preview'] = $url_customize_preview;
    $variables['#attached']['library'][] = 'gaviasthemer/customize';

    $variables['#attached']['drupalSettings']['gavias_customize']['json'] = $json;

    $variables['addon_template'] = '';
    $variables['save_customize_permission'] = 'hidden';
    $variables['fonts'] = gavias_edmix_render_option_font();
    $variables['patterns'] = gavias_edmix_options_patterns();
    if(file_exists($theme_path . '/templates/addon/skins.html.twig')){
      $variables['addon_template'] = $theme_path . '/templates/addon/skins.html.twig';
    }

    if($user->hasPermission('administer gavias_customize')){
      $variables['save_customize_permission'] = 'show';
    }

    $current_url = $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"];
    $current_url = preg_replace('/([?&])display=[^&]+(&|$)/','$2',$current_url);
    if(strpos($current_url, '?')){
       $current_url .= '&';
    }
    else{
       $current_url .= '?';
    }
    $variables['current_url'] = $current_url;
  }

  //---- End customize gavias themer ---

  $skin = 'default';
  $skin = theme_get_setting('theme_skin');
  if(isset($_GET['gvas']) && $_GET['gvas']){
    $skin = $_GET['gvas'];
  }
  if(empty($skin)){
    $skin = 'default';
  }
  $variables['#attached']['library'][] = 'gavias_edmix/gavias_edmix.skin.' . $skin;

  // Add information about the number of sidebars.
  if (!empty($variables['page']['sidebar_first']) && !empty($variables['page']['sidebar_second'])) {
    $variables['attributes']['class'][] = 'layout-two-sidebars';
  }
  elseif (!empty($variables['page']['sidebar_first'])) {
    $variables['attributes']['class'][] = 'layout-one-sidebar';
    $variables['attributes']['class'][] = 'layout-sidebar-first';
  }
  elseif (!empty($variables['page']['sidebar_second'])) {
    $variables['attributes']['class'][] = 'layout-one-sidebar';
    $variables['attributes']['class'][] = 'layout-sidebar-second';
  }
  else {
    $variables['attributes']['class'][] = 'layout-no-sidebars';
  }

  $variables['attributes']['class'][] = theme_get_setting('site_layout');
  $variables['attributes']['class'][] = theme_get_setting('footer_fixed');



  if(theme_get_setting('preloader') == '1'){
    $variables['attributes']['class'][] = 'js-preloader';
  }else{
    $variables['attributes']['class'][] = 'not-preloader';
  }

  if(\Drupal::service('module_handler')->moduleExists('gavias_hook_themer')){
    $abs_url_config = \Drupal::url('gavias_hook_themer.ajax_views', array(), array('absolute' => FALSE));
  }else{
    $abs_url_config = '';
  }
  $variables['#attached']['drupalSettings']['gavias_load_ajax_view'] = $abs_url_config;
}

function gavias_edmix_preprocess_field(&$variables) {
  global $base_url;
  $theme_path = drupal_get_path('theme', 'gavias_edmix');
  $variables['theme_path'] = $base_url . '/' . $theme_path;
}

/**
 * Implements hook_preprocess_HOOK() for maintenance-page.html.twig.
 */
function gavias_edmix_preprocess_maintenance_page(&$variables) {
  if (!$variables['db_is_active']) {
    $variables['site_name'] = '';
  }
}

/**
 * Implements hook_preprocess_HOOK() for block.html.twig.
 */
function gavias_edmix_preprocess_block(&$variables) {
  // Add a clearfix class to system branding blocks.

  if ($variables['plugin_id'] == 'system_branding_block') {
    $variables['attributes']['class'][] = 'clearfix';
    $tmp_logo = theme_get_setting('logo');
    $variables['setting_logo'] = $tmp_logo['use_default'];
  }
}

function gavias_edmix_preprocess_block__system_breadcrumb_block(&$variables){
  $styles = array();
  $bg_image = base_path() . drupal_get_path('theme', 'gavias_edmix') . '/images/breadcrumb.jpg';
  $bg_color = '';
  $bg_position = 'center center';
  $bg_repeat = 'no-repeat';
  $text_style = 'text-light';

  if (!empty($variables['elements']['#id'])) {
    $block = Block::load($variables['elements']['#id']);
    if($variables['plugin_id'] == 'system_breadcrumb_block'){
      $_id = $variables['elements']['#id'];
      $breadcrumb_background_color = $block->getThirdPartySetting('gaviasthemer', 'breadcrumb_background_color');
      $breadcrumb_background_position = $block->getThirdPartySetting('gaviasthemer', 'breadcrumb_background_position');
      $breadcrumb_background_repeat = $block->getThirdPartySetting('gaviasthemer', 'breadcrumb_background_repeat');
      $breadcrumb_color_style = $block->getThirdPartySetting('gaviasthemer', 'breadcrumb_color_style');
      $breadcrumb_background_image_path = \Drupal::config('gaviasthemer.settings')->get('breadcrumb_background_image_path_' . $_id);
      if($breadcrumb_color_style){
        $text_style = $breadcrumb_color_style;
      }
      if($breadcrumb_background_color){
        $bg_color = $breadcrumb_background_color;
      }
      if($breadcrumb_background_position){
        $bg_position = $breadcrumb_background_position;
      }
      if($breadcrumb_background_repeat){
        $bg_repeat = $breadcrumb_background_repeat;
      }
      if($breadcrumb_background_image_path){
        $bg_image = file_create_url($breadcrumb_background_image_path);
      }
    }
  }

  $variables['attributes']['class'][] = $text_style;
  if($bg_color){
    $styles[] = "background-color: {$bg_color};";
  }
  if($bg_image){
    $styles[] = "background-image: url('{$bg_image}');";
  }
  $styles[] = "background-position: {$bg_position};";
  $styles[] = "background-repeat: {$bg_repeat};";
  $variables['custom_style'] = implode('', $styles);

  //Breadcrumb title
  $title = '';
  $request = \Drupal::request();
  $title = '';
  if ($route = $request->attributes->get(\Symfony\Cmf\Component\Routing\RouteObjectInterface::ROUTE_OBJECT)) {
    $title = \Drupal::service('title_resolver')->getTitle($request, $route);
  }
  $variables['breadcrumb_title'] = $title;
}


/**
 * Implements hook_preprocess_HOOK() for page templates.
 */
function gavias_edmix_preprocess_page_title(&$variables) {
  // Since the title and the shortcut link are both block level elements,
  // positioning them next to each other is much simpler with a wrapper div.
  if (!empty($variables['title_suffix']['add_or_remove_shortcut']) && $variables['title']) {
    // Add a wrapper div using the title_prefix and title_suffix render
    // elements.
    $variables['title_prefix']['shortcut_wrapper'] = array(
      '#markup' => '<div class="shortcut-wrapper clearfix">',
      '#weight' => 100,
    );
    $variables['title_suffix']['shortcut_wrapper'] = array(
      '#markup' => '</div>',
      '#weight' => -99,
    );
    // Make sure the shortcut link is the first item in title_suffix.
    $variables['title_suffix']['add_or_remove_shortcut']['#weight'] = -100;
  }
}


/**
 * Implements hook_theme_suggestions_HOOK_alter() for form templates.
 */
function gavias_edmix_theme_suggestions_form_alter(array &$suggestions, array $variables) {
  if ($variables['element']['#form_id'] == 'search_block_form') {
    $suggestions[] = 'form__search_block_form';
  }
}

/**
 * Implements hook_form_alter() to add classes to the search form.
 */
function gavias_edmix_form_alter(&$form, \Drupal\Core\Form\FormStateInterface $form_state, $form_id) {
  if (in_array($form_id, ['search_block_form', 'search_form'])) {
    $key = ($form_id == 'search_block_form') ? 'actions' : 'basic';
    if (!isset($form[$key]['submit']['#attributes'])) {
      $form[$key]['submit']['#attributes'] = new Attribute();
    }
    $form[$key]['submit']['#attributes']->addClass('search-form__submit');
  }
}

function gavias_edmix_theme_suggestions_page_alter(array &$suggestions, array $variables) {
  if ($node = \Drupal::request()->attributes->get('node')) {
    if($node->hasField('gva_node_layout')){
      $layout = $node->get('gva_node_layout')->value;
      if($layout){
        array_splice($suggestions, 1, 0, 'page__layout__' . $layout);
      }else{
        array_splice($suggestions, 1, 0, 'page__node__' . $node->getType());
      }
    }else{
      array_splice($suggestions, 1, 0, 'page__node__' . $node->getType());
    }
  }


/*  $node = \Drupal::routeMatch()->getParameter('node');
  if ($node instanceof \Drupal\node\NodeInterface) {
    $suggestions[] = 'page__' . $node->bundle();
    $suggestions[] = 'page__node_' . $node->bundle();
  }
*/

}

function gavias_edmix_theme_suggestions_field_alter(&$suggestions, $variables) {
  $suggestions[] = 'field__' .
  $variables['element']['#field_name'] . '__' .
  $variables['element']['#view_mode'];
}

