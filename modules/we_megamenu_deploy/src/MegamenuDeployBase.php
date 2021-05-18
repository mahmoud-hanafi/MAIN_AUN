<?php

namespace Drupal\we_megamenu_deploy;

use Drupal\Core\Database\Connection;
use Drupal\Core\Site\Settings;
use Drupal\default_content\ScannerInterface;

/**
 * Class MegamenuDeployBase.
 *
 * @package Drupal\we_megamenu_deploy
 */
class MegamenuDeployBase {

  /**
   * Contains database connection.
   *
   * @var \Drupal\Core\Database\Connection
   *   Database connection.
   */
  protected $database;

  /**
   * Settings.
   *
   * @var \Drupal\Core\Site\Settings
   *   Settings.
   */
  protected $settings;

  /**
   * Default deploy scanner to get folder files.
   *
   * @var \Drupal\default_content\ScannerInterface
   *   Scanner.
   */
  protected $scanner;

  /**
   * MegamenuDeployBase constructor.
   *
   * @param \Drupal\Core\Site\Settings $settings
   *   Settings.
   * @param \Drupal\Core\Database\Connection $database
   *   Database connection.
   * @param \Drupal\default_content\ScannerInterface $scanner
   *   Default deploy scanner.
   */
  public function __construct(Settings $settings, Connection $database, ScannerInterface $scanner) {
    $this->settings = $settings;
    $this->database = $database;
    $this->scanner = $scanner;
  }

  /**
   * Get content folder.
   *
   * Folder is automatically created on install inside files folder.
   * Or you can override content folder in settings.php file.
   *
   * @return string
   *   Return path to the content folder.
   *
   * @example Recommended usage:
   *   $settings['we_megamenu_deploy_content'] =  '../content/we_megamenu';
   */
  protected function getContentFolder() {
    if ($contentDir = $this->settings->get('we_megamenu_deploy_content')) {
      return $contentDir;
    };
    return '';
  }

  /**
   * Helper to create json file.
   *
   * @param string $path
   *   We megamenu deploy directory which to write the file.
   * @param string $menu_name
   *   Menu name, to be used as filename.
   * @param string $serialized_object
   *   The serialized object to write.
   */
  protected function putFile($path, $menu_name, $serialized_object) {
    file_put_contents($path . '/wmm-' . $menu_name . '.json', $serialized_object);
  }

  /**
   * Helper to retrieve json file content.
   *
   * @param string $file_path
   *   We megamenu deploy directory which to write the file.
   *
   * @return mixed
   *   Return megamenu json object.
   */
  protected function getFile($file_path) {
    if (!file_exists($file_path)) {
      $error_msg = t('The file @file not exist', [
        '@file' => $file_path,
      ]);
      \Drupal::logger('we_megamenu_deploy')->error($error_msg);
      return FALSE;
    }
    return file_get_contents($file_path);
  }

  /**
   * Retrieve files from we megamenu deploy folder.
   */
  protected function getMenuFiles() {
    $path = $this->getContentFolder();
    return $this->scanner->scan($path);
  }

}
