<?php

/**
 * @file
 * Contains functions to add custom drush 8 commands.
 */

/**
 * Implements hook_drush_command().
 */
function we_megamenu_deploy_drush_command() {
	// We_megamenu export command.
  $commands['we-megamenu-deploy-export'] = [
    'description' => 'Export We Megamenu configuration, for one or many menus.',
    'aliases' => ['mmde'],
    'arguments' => [
       'menu_name' => 'Menu name.',
     ],
     'examples' => [
       'drush mmde' => 'Export all we_megamenu menus.',
       'drush mmde footer' => 'Eport only we_megamenu "footer".',
     ],
  ];
  // We_megamenu import command.
  $commands['we-megamenu-deploy-import'] = [
    'description' => 'Import all We Megamenu menus configuration.',
    'aliases' => ['mmdi'],
    'arguments' => [
       'menu_name' => 'Menu name.',
     ],
     'options' => [
       'force-update' => 'Folder to export to, entities are grouped by entity type into directories.',
     ],
     'examples' => [
       'drush mmdi' => 'Import all we_megamenu menus configuration.',
       'drush mmdi --force-update' => 'Import all we_megamenu menus configuration and update it if exists.',
     ],
  ];

  return $commands;
}

/**
 * Drush command to export We Megamenu configuration, for one or many menus.
 */
function drush_we_megamenu_deploy_export($menu_name = NULL) {
  $count = Drupal::service('we_megamenu_deploy.exporter')->exportMenus($menu_name);
  drush_print(dt('@count menu(s) has been processed.', ['@count' => $count]));
}

/**
 * Drush command to import We Megamenu configuration.
 */
function drush_we_megamenu_deploy_import($menu_name = NULL) {
	$forceUpdate = drush_get_option('force-update', FALSE);
	// Importer service.
  $count = Drupal::service('we_megamenu_deploy.importer')->importMenus($forceUpdate);
  // Print processed menus.
  drush_print(dt("@count menu(s) has been created.", [
  	'@count' => $count['created'],
  ]));
  drush_print(dt("@count menu(s) has been updated.", [
  	'@count' => $count['updated'],
  ]));
  drush_print(dt("@count menu(s) has been skipped.", [
  	'@count' => $count['skipped'],
  ]));
}
