<?php

namespace Drupal\we_megamenu_deploy;

use Drupal\Component\Serialization\Json;

/**
 * Class Exporter.
 *
 * @package Drupal\we_megamenu_deploy
 */
class Exporter extends MegamenuDeployBase {

  /**
   * Function export menus in a json files.
   *
   * @param string $menuName
   *   The name of menu : Example => Footer.
   *
   * @return int
   *   Return the number of exported menus.
   */
  public function exportMenus($menuName = NULL) {
    $path = $this->getContentFolder();
    $count = 0;
    $query = $this->database->select('we_megamenu', 'wm');
    $query->fields('wm', ['menu_name', 'theme', 'data_config']);
    if (!empty($menuName)) {
      $query->condition('wm.menu_name', $menuName);
    }
    $results = $query->execute()->fetchAll();
    foreach ($results as $result) {
      $json = Json::encode($result);
      $this->putFile($path, $result->menu_name, $json);
      $count++;
    }
    return $count;
  }

}
