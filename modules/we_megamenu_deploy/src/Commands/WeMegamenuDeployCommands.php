<?php

namespace Drupal\we_megamenu_deploy\Commands;

use Drupal\we_megamenu_deploy\Exporter;
use Drupal\we_megamenu_deploy\Importer;
use Drush\Commands\DrushCommands;

/**
 * Class WeMegamenuDeployCommands.
 *
 * @package Drupal\we_megamenu_deploy\Commands
 */
class WeMegamenuDeployCommands extends DrushCommands {

  /**
   * We megamenu deploy exporter service.
   *
   * @var \Drupal\we_megamenu_deploy\Exporter
   */
  private $exporter;

  /**
   * We megamenu deploy importer service.
   *
   * @var \Drupal\we_megamenu_deploy\Importer
   */
  private $importer;

  /**
   * WeMegamenuDeployCommands constructor.
   *
   * @param \Drupal\we_megamenu_deploy\Exporter $exporter
   *   We megamenu deploy exporter.
   * @param \Drupal\we_megamenu_deploy\Importer $importer
   *   We megamenu deploy importer.
   */
  public function __construct(Exporter $exporter, Importer $importer) {
    $this->exporter = $exporter;
    $this->importer = $importer;
  }

  /**
   * Export We Megamenu configuration, for one or many menus.
   *
   * @param string $menu_name
   *   WeMegamenu menu name.
   *
   * @command we_megamenu_deploy:export
   * @aliases mmde
   * @usage we_megamenu_deploy:export footer
   *   Export we_megemenu footer configuration.
   */
  public function export($menu_name = NULL) {
    $count = $this->exporter->exportMenus($menu_name);
    $this->output()->writeln("{$count} menu(s) has been processed.");
  }

  /**
   * Import all exported menus.
   *
   * @command we_megamenu_deploy:import
   * @aliases mmdi
   * @option force_update Folder to export to, entities are grouped by entity type into directories.
   * @usage we_megamenu_deploy:import --force-update=TRUE
   *   Import we_megemenu menus configuration.
   */
  public function import($options = ['force-update' => FALSE]) {
    $count = $this->importer->importMenus($options['force-update']);
    $this->output()->writeln("{$count['created']} menu(s) has been created.");
    $this->output()->writeln("{$count['updated']} menu(s) has been updated.");
    $this->output()->writeln("{$count['skipped']} menu(s) has been skipped.");
  }

}
