<?php

namespace Drupal\we_megamenu_deploy;

use Drupal\Component\Serialization\Json;

/**
 * Class Importer.
 *
 * @package Drupal\we_megamenu_deploy
 */
class Importer extends MegamenuDeployBase {

  /**
   * Import menus from json files.
   *
   * @param bool $force_update
   *   Force updating of menu if existed.
   *
   * @return array
   *   Count.
   *
   * @throws \Exception
   */
  public function importMenus($force_update = FALSE) {
    $files = $this->getMenuFiles();
    $count = ['skipped' => 0, 'created' => 0, 'updated' => 0];
    foreach ($files as $file) {
      $object = $this->menuObject($file);
      if (empty($object)) {
        $count['skipped']++;
        continue;
      }
      // Check menu existence.
      $exist = $this->menuExist($object);
      // Update we megameu settings to database if existing.
      if ($exist == TRUE && $force_update == TRUE) {
        $this->updateMenu($object);
        $count['updated']++;
      }
      // Insert we megameu settings to database if not existing.
      if ($exist == FALSE) {
        $this->insertMenu($object);
        $count['created']++;
      }
    }
    return $count;
  }

  /**
   * Checking if menu exist in database.
   *
   * @param object $object
   *   Menu object.
   *
   * @return int|void
   *   Return if menu exist.
   */
  public function menuExist($object) {
    $query = $this->database->select('we_megamenu', 'wm');
    $query->fields('wm', ['menu_name'])
      ->condition('wm.menu_name', $object->menu_name)
      ->condition('wm.theme', $object->theme);
    return (count($query->execute()->fetchAll()) > 0);
  }

  /**
   * Retrieve data from json file.
   *
   * @param mixed $file
   *   File object.
   *
   * @return mixed
   *   Return array contains we megamenu deploy data.
   */
  public function menuObject($file) {
    $json = $this->getFile($file->uri);
    if (empty($json)) {
      return FALSE;
    }
    $object = Json::decode($json);
    return (object) $object;
  }

  /**
   * Insert we_megamenu in Database.
   *
   * @param mixed $object
   *   Menu object.
   *
   * @return \Drupal\Core\Database\StatementInterface|int|null
   *   Return menu.
   *
   * @throws \Exception
   */
  protected function insertMenu($object) {
    $query = $this->database->insert('we_megamenu')
      ->fields(['menu_name', 'theme', 'data_config'])
      ->values([
        'menu_name' => $object->menu_name,
        'theme' => $object->theme,
        'data_config' => $object->data_config,
      ]);
    return $query->execute();
  }

  /**
   * Insert we_megamenu in Database.
   *
   * @param mixed $object
   *   Menu object.
   *
   * @return \Drupal\Core\Database\StatementInterface|int|null
   *   Return menu object
   */
  protected function updateMenu($object) {
    $query = $this->database->update('we_megamenu')
      ->fields(['data_config' => $object->data_config])
      ->condition('menu_name', $object->menu_name)
      ->condition('theme', $object->theme);
    return $query->execute();
  }

}
