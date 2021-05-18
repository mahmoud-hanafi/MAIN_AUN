<?php

namespace Drupal\simple_node_importer\Controller;

use Drupal\simple_node_importer\Services\GetServices;
use Drupal\Component\Render\FormattableMarkup;
use Drupal\node\NodeInterface;
use Drupal\user\Entity\User;
use Drupal\Core\Database\Database;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\TempStore\PrivateTempStoreFactory;
use Drupal\Core\Session\SessionManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;
use Drupal\node\Entity\Node;

/**
 * Default controller for the simple_node_importer module.
 */
class NodeImportController extends ControllerBase {

  protected $services;
  protected $sessionVariable;
  protected $sessionManager;
  protected $currentUser;

  /**
   * Responsible for node type entity immport.
   *
   * @param Drupal\simple_node_importer\Services\GetServices $getServices
   *   Constructs a Drupal\simple_node_importer\Services object.
   * @param Drupal\Core\TempStore\PrivateTempStoreFactory $sessionVariable
   *   Constructs a Drupal\Core\TempStore\PrivateTempStoreFactory object.
   * @param Drupal\Core\Session\SessionManagerInterface $session_manager
   *   Constructs a Drupal\Core\Session\SessionManagerInterface object.
   * @param Drupal\Core\Session\AccountInterface $current_user
   *   Constructs a Drupal\Core\Session\AccountInterface object.
   */
  public function __construct(GetServices $getServices, PrivateTempStoreFactory $sessionVariable, SessionManagerInterface $session_manager, AccountInterface $current_user) {
    $this->services = $getServices;
    $this->sessionVariable = $sessionVariable->get('simple_node_importer');
    $this->sessionManager = $session_manager;
    $this->currentUser = $current_user;
  }

  /**
   * Creates node for specified type of mapped data.
   */
  public static function simpleNodeCreate($records, &$context) {

    $user = "";
    $entity_type = 'node';
    $userAutoCreate = \Drupal::config('simple_node_importer.settings')->get('simple_node_importer_allow_user_autocreate');
    foreach ($records as $record) {

      $batch_result['result'] = '';
      if (!empty($record['uid'])) {
        if (filter_var($record['uid'], FILTER_VALIDATE_EMAIL)) {
          $user = \Drupal::service('snp.get_services')->getUserByEmail($record['uid'], $userAutoCreate);
        }
        else {
          $user = \Drupal::service('snp.get_services')->getUserByUsername($record['uid'], $userAutoCreate);
        }
      }
      else {
        if ($userAutoCreate == 'admin') {
          $user = 1;
        }
        elseif ($userAutoCreate == 'current') {
          $user = \Drupal::currentUser();
        }
        else {
          $batch_result['result'] = $record;
        }
      }

      // Assigning user id to node.
      if ($user && !is_int($user)) {
        $uid = $user->id();
      }
      else {
        $uid = $user;
      }

      if (empty($record['title'])) {
        $batch_result['result'] = $record;
      }

      $node_data = [
        'type' => $record['type'],
        'title' => !empty($record['title']) ? $record['title'] : '',
        'uid' => $uid,
        'status' => ($record['status'] == 1 || $record['status'] == TRUE) ? TRUE : FALSE,
      ];
      $field_names = array_keys($record);

      if (empty($batch_result['result'])) {
        $batch_result = \Drupal::service('snp.get_services')->checkFieldWidget($field_names, $record, $node_data, $entity_type);
      }
      if (!empty($batch_result['result'])) {
        if (!isset($context['results']['failed'])) {
          $context['results']['failed'] = 0;
        }
        $context['results']['failed']++;
        $batch_result['result']['sni_id'] = $context['results']['failed'];
        $context['results']['sni_nid'] = $record['nid'];
        $context['results']['data'][] = serialize($batch_result['result']);
      }
      else {
        $node = Node::create($batch_result);
        $node->save();
        if ($node->id()) {
          if (!isset($context['results']['created'])) {
            $context['results']['created'] = 0;
          }
          $context['results']['created']++;
        }
        else {
          $context['results']['failed']++;
          $batch_result['result']['sni_id'] = $context['results']['failed'];
          $context['results']['sni_nid'] = $record['nid'];
          $context['results']['data'] = $batch_result['result'];
        }
      }
    }
  }

  /**
   * Callback : Called when batch process is finished.
   */
  public static function nodeImportBatchFinished($success, $results, $operations) {
    if ($success) {
      $rclink = Url::fromRoute('simple_node_importer.node_resolution_center', [], ['absolute' => TRUE]);
      $link = $rclink->toString();
      $created_count = !empty($results['created']) ? $results['created'] : NULL;
      $failed_count = !empty($results['failed']) ? $results['failed'] : NULL;

      if ($created_count && !$failed_count) {
        $import_status = new FormattableMarkup("Nodes successfully created: @created_count", ['@created_count' => $created_count]);
      }
      elseif (!$created_count && $failed_count) {
        $import_status = new FormattableMarkup('Nodes import failed: @failed_count. To view failed records, please visit <a href="@link">Resolution Center</a>', ['@failed_count' => $failed_count, '@link' => $link]);
      }
      else {
        $import_status = new FormattableMarkup('Nodes successfully created: @created_count<br/>Nodes import failed: @failed_count<br/>To view failed records, please visit <a href="@link">Resolution Center</a>',
          [
            '@created_count' => $created_count,
            '@failed_count' => $failed_count,
            '@link' => $link,
          ]
        );
      }
      if (isset($results['failed']) && !empty($results['failed'])) {
        // Add Failed nodes to Resolution Table.
        NodeImportController::addFailedRecordsInRc($results);
      }

      // $statusMessage = ;.
      drupal_set_message(t('Node import completed! Import status:<br/> @import_status', ['@import_status' => $import_status]));
    }
    else {
      $error_operation = reset($operations);
      $message = new FormattableMarkup('An error occurred while processing %error_operation with arguments: @arguments', [
        '%error_operation' => $error_operation[0],
        '@arguments' => print_r($error_operation[1], TRUE),
      ]);
      drupal_set_message($message, 'error');
    }

    return new RedirectResponse(\Drupal::url('<front>'));
  }

  /**
   * Add data to node resolution table.
   */
  public static function addFailedRecordsInRc($result) {
    if (isset($result['data']) && !empty($result['data'])) {

      $import_status = [
        'success' => !empty($result['created']) ? $result['created'] : "",
        'fail' => !empty($result['failed']) ? $result['failed'] : "",
      ];
      $sni_nid = !empty($result['sni_nid']) ? $result['sni_nid'] : NULL;
      foreach ($result['data'] as $data) {
        $conn = Database::getConnection();
        $resolution_log = $conn->insert('node_resolution')->fields(
          [
            'sni_nid' => $sni_nid,
            'data' => $data,
            'reference' => \Drupal::service('snp.get_services')->generateReference(10),
            'status' => serialize($import_status),
            'created' => REQUEST_TIME,
          ]
        )->execute();
      }

      if ($resolution_log) {
        drupal_set_message(t('Failed records added to resolution center.'));
      }
    }
  }

  /**
   * Prepare Resolution Center Page.
   */
  public function viewResolutionCenter() {
    $tableheader = [
      ['data' => $this->t('Sr no')],
      ['data' => $this->t('Bundle')],
      [
        'data' => $this->t('Date of import'),
      ],
      ['data' => $this->t('Successful')],
      ['data' => $this->t('Failures')],
      [
        'data' => $this->t('Uploaded By'),
      ],
      ['data' => $this->t('Operations')],
    ];
    // A variable to hold the row information for each table row.
    $rows = [];
    $srno = 1;
    $connection = Database::getConnection();
    $connection->query("SET SQL_MODE=''");
    $query_record = $connection->select('node_field_data', 'n');
    $query_record->innerJoin('node_resolution', 'nr', 'n.nid = nr.sni_nid');
    $query_record->fields('n', ['nid', 'uid', 'type', 'created']);
    $query_record->fields(
      'nr',
      [
        'sni_nid',
        'data',
        'reference',
        'status',
        'created',
        'changed',
      ]
    );
    $query_record->groupBy('nr.sni_nid');

    $result = $query_record->execute()->fetchAll();

    foreach ($result as $data) {
      $serializData = unserialize($data->data);
      $contentType = $serializData['type'];
      $row = [];
      $row[] = ['data' => $srno];

      // Get the bundle label.
      if ($contentType == 'user') {
        $bundle_label = 'User';
      }
      else {
        $node = \Drupal::entityManager()->getStorage('node')->load($data->nid);
        $bundle_label = \Drupal::entityTypeManager()->getStorage('node_type')->load($contentType)->label();
      }

      $row[] = ['data' => $bundle_label];

      // Convert timestamp to date & time.
      $formatted_date = date('d-M-Y', $data->created);
      $row[] = ['data' => $formatted_date];
      $status = unserialize($data->status);
      $row[] = ['data' => ($status['success']) ? $status['success'] : 0];
      $row[] = ['data' => $status['fail']];
      // Pass your uid.
      $account = User::load($data->uid);
      $author = $account->getUsername();
      $row[] = ['data' => $author];

      // Generate download csv link.
      $generateDownloadLink = Url::fromRoute('simple_node_importer.resolution_center_operations', ['node' => $data->nid, 'op' => 'download-csv'], ['absolute' => TRUE]);
      $csvLink = $generateDownloadLink->toString();

      // Generate delete node link.
      $generateDeleteLink = Url::fromRoute('entity.node.delete_form', ['node' => $data->nid], ['absolute' => TRUE]);
      $deleteLink = $generateDeleteLink->toString();

      // Generate view records link.
      $generateViewLink = Url::fromRoute('simple_node_importer.resolution_center_operations', ['node' => $data->nid, 'op' => 'view-records'], ['absolute' => TRUE]);
      $viewLink = $generateViewLink->toString();

      $operationGenerator = new FormattableMarkup('<a href="@csvLink">Download CSV</a> | <a href="@viewLink">View</a> | <a href="@deleteLink">Delete</a>',
      [
        "@csvLink" => $csvLink,
        "@viewLink" => $viewLink,
        "@deleteLink" => $deleteLink,
      ]);

      $row[] = [
        'data' => $this->t("@operations", ["@operations" => $operationGenerator]),
      ];

      $srno++;
      $rows[] = ['data' => $row];
    }

    if (!empty($rows)) {
      $output = [
        '#type' => 'table',
        '#header' => $tableheader,
        '#rows' => $rows,
      ];
    }
    else {
      $output = [
        '#type' => 'table',
        '#header' => $tableheader,
        '#empty' => $this->t('There are no items yet. <a href="@add-url">Add an item.</a>', [
          '@add-url' => Url::fromRoute('node.add', ['node_type' => 'simple_node'])->toString(),
        ]),
      ];
    }

    return $output;
  }

  /**
   * Provides different operations for failed rows.
   */
  public function resolutionCenterOperations(NodeInterface $node, $op) {

    $failed_rows = NodeImportController::getFailedRowsInRc($node->id(), NULL);

    if ($failed_rows) {
      $i = 1;
      foreach ($failed_rows as $col_val) {
        unset($col_val['sni_nid']);
        foreach ($col_val as $keycol => $keyfieldval) {
          if (is_array($keyfieldval) && !empty($keyfieldval)) {

            $j = 0;
            foreach ($keyfieldval as $keyfield) {
              if ($j == 0) {
                $col_val[$keycol] = $keyfield;
              }
              elseif (!empty($keyfield)) {
                $col_val[$keycol . "_" . $j] = $keyfield;
              }
              $j++;
            }
          }
          else {
            $col_val[$keycol] = $keyfieldval;
          }
        }

        $rows[] = $col_val;
        $i++;
      }
    }

    $entityType = $node->field_select_entity_type[0]->value;

    if ($op == 'download-csv') {

      if ($entityType == 'user') {
        $filename = 'Import-failed-users.csv';
      }
      else {
        $filename = 'Import-failed-nodes.csv';
      }
      header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
      header('Content-Description: File Transfer');
      header("Content-type: text/csv");
      header("Content-Disposition: attachment; filename={$filename}");
      header("Expires: 0");
      header("Pragma: public");
      $fh = @fopen('php://output', 'w');

      // Make sure nothing else is sent, our file is done.
      $header_update = FALSE;
      foreach ($rows as $val) {
        $row = [];
        if (!empty($val['type'])) {
          unset($val['type']);
        }

        if (!empty($val['reference'])) {
          unset($val['reference']);
        }

        foreach ($val as $key => $keyval) {
          if (!$header_update) {
            $headcol[] = ucwords(str_replace("field ", "", str_replace("_", " ", $key)));
          }
          $row[] = $keyval;
        }

        if (!$header_update) {
          fputcsv($fh, $headcol);
          $header_update = TRUE;
        }

        fputcsv($fh, $row);
      }

      fclose($fh);
      exit();
    }
    elseif ($op == 'view-records') {
      $srno = 1;
      $tableheader = [
        ['data' => $this->t('Sr no')],
        ['data' => ($entityType == 'user') ? $this->t('Username') : $this->t('Title')],
        ['data' => $this->t('Operations')],
      ];

      $defaultMsg = $this->t("<strong>Value not provided in CSV</strong>");

      foreach ($rows as $val) {
        $row = [];
        foreach ($val as $key => $keyval) {
          if ($key == 'title' || $key == 'name') {
            $row[] = ['data' => $srno];
            $row[] = empty($keyval) ? $defaultMsg : ['data' => $keyval];
            break;
          }
        }

        // Generate add node link.
        if ($entityType == 'user') {
          $addLink = Url::fromRoute('user.admin_create',
            [
              'entity_type' => $entityType,
              'refkey' => $val['reference'],
              'bundle' => $val['type'],
            ],
            [
              'absolute' => TRUE,
            ]
          );
          $generateAddLink = $addLink->toString();
        }
        else {
          $addLink = Url::fromRoute('node.add',
            [
              'node_type' => $val['type'],
              'entity_type' => $entityType,
              'refkey' => $val['reference'],
              'bundle' => $val['type'],
            ],
            [
              'absolute' => TRUE,
            ]
          );
          $generateAddLink = $addLink->toString();
        }

        $row[] = [
          'data' => $this->t('<a href="@addLink">Edit & Save</a>', ["@addLink" => $generateAddLink]),
        ];

        $srno++;
        $failedRows[] = ['data' => $row];
      }

      // Output as table format.
      $output = [
        '#type' => 'table',
        '#header' => $tableheader,
        '#rows' => $failedRows,
      ];

      return $output;
    }
  }

  /**
   * Function to abort the import.
   */
  public function snpDeleteNode($option, $node) {
    if ($node) {
      $storage_handler = \Drupal::entityTypeManager()->getStorage("node");
      $entity = $storage_handler->load($node);
      $storage_handler->delete([$entity]);
      $response = new RedirectResponse('/node/add/simple_node');
      return $response->send();
    }
  }

  /**
   * Function to fetch failed nodes from node_resolution table.
   *
   * @param int $nid
   *   Failed nodes from import node nid.
   * @param string $refKey
   *   Reference key for the failed records.
   */
  public static function getFailedRowsInRc($nid = NULL, $refKey = NULL) {

    $data = [];

    // Query to fetch failed data.
    $connection = Database::getConnection();
    $connection->query("SET SQL_MODE=''");
    $query_record = $connection->select('node_resolution', 'nr');
    $query_record->fields('nr', ['data', 'reference', 'sni_nid']);

    if (!empty($nid)) {
      $query_record->condition('nr.sni_nid', $nid);
    }

    if (!empty($refKey)) {
      $query_record->condition('nr.reference', $refKey);
    }

    $result = $query_record->execute()->fetchAll();
    foreach ($result as $k => $value) {
      // code...
      $data[$k] = unserialize($value->data);
      $reference[$k] = $value->reference;
      $sni_nid[$k] = $value->sni_nid;
      unset($data[$k]['sni_id']);
    }

    foreach ($data as $rowKey => $rows) {
      if (!empty($rows['nid']) || !empty($rows['type'])) {
        unset($rows['nid']);
        // unset($rows['type']);.
      }
      foreach ($rows as $key => $record) {
        $records[$rowKey][$key] = $record;
        $records[$rowKey]['reference'] = $reference[$rowKey];
        $records[$rowKey]['sni_nid'] = $sni_nid[$rowKey];
      }
    }

    if (!empty($records)) {
      return $records;
    }
    else {
      return FALSE;
    }
  }

  /**
   * Callback function for Page Title.
   */
  public function resolutionCenterTitleCallback(NodeInterface $node, $op) {
    if ($op == 'view-records') {
      return 'Resolution Center - View records';
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('snp.get_services'),
      $container->get('user.private_tempstore'),
      $container->get('session_manager'),
      $container->get('current_user')
    );
  }

}
