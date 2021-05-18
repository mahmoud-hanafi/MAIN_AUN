<?php

namespace Drupal\o365_onedrive;

use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\o365\GraphService;

/**
 * Class GetFilesAndFoldersService.
 */
class GetFilesAndFoldersService implements GetFilesAndFoldersServiceInterface {

  /**
   * Drupal\o365\GraphService definition.
   *
   * @var \Drupal\o365\GraphService
   */
  protected $graphService;

  /**
   * The drive array with all the values.
   *
   * @var array
   */
  protected $drive;

  /**
   * Constructs a new GetFilesAndFoldersService object.
   *
   * @param \Drupal\o365\GraphService $o365_graph
   *   The GraphService definition.
   */
  public function __construct(GraphService $o365_graph) {
    $this->graphService = $o365_graph;
  }

  /**
   * {@inheritdoc}
   */
  public function listFilesAndFolders($folder = FALSE) {
    $endPoint = '/me/drive/root/children';
    if ($folder) {
      $endPoint = '/me/drive/items/' . $folder . '/children';
    }

    $this->getDrive($endPoint);
    return $this->renderFileList();
  }

  /**
   * {@inheritdoc}
   */
  public function listSharedFilesAndFolders($limit = 10) {
    $endPoint = '/me/drive/sharedWithMe?$top=' . $limit;

    $this->getDrive($endPoint);
    return $this->renderFileList();
  }

  /**
   * {@inheritdoc}
   */
  public function listSpecialFilesAndFolders($type) {
    $endPoint = '/me/drive/' . $type;

    $this->getDrive($endPoint);
    return $this->renderFileList();
  }

  /**
   * Get the drive contents.
   *
   * @param string $endPoint
   *   The endpoint we want the content from.
   *
   * @throws \Drupal\Core\TempStore\TempStoreException
   * @throws \League\OAuth2\Client\Provider\Exception\IdentityProviderException
   */
  private function getDrive($endPoint) {
    $this->drive = $this->graphService->getGraphData($endPoint);
  }

  /**
   * Render the list of files and folders.
   *
   * @return array
   *   The render array for the files and folders.
   */
  protected function renderFileList() {
    $listItems = [];

    foreach ($this->drive['value'] as $value) {
      if (isset($value['file'])) {
        $options = [
          'attributes' => [
            'target' => '_blank',
          ],
        ];
        $url = Url::fromUri($value['webUrl'], $options);
        $listItems[] = [
          '#markup' => Link::fromTextAndUrl($value['name'], $url)->toString(),
          '#wrapper_attributes' => [
            'class' => [
              'o365__links o365__links__file',
            ],
          ],
        ];
      }
      else {
        $listItems[] = [
          '#markup' => Link::createFromRoute($value['name'], 'o365_onedrive.one_drive_list_folder', ['folder' => $value['id']])
            ->toString(),
          '#wrapper_attributes' => [
            'class' => [
              'o365__links o365__links__folder',
            ],
          ],
        ];
      }
    }

    $value = [
      '#theme' => 'item_list',
      '#items' => $listItems,
    ];

    $value['#attached']['library'][] = 'o365_onedrive/o365_onedrive';
    return $value;
  }

}
