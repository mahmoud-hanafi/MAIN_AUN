<?php

namespace Drupal\simple_node_importer\Controller;

use Drupal\Component\Render\FormattableMarkup;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;
use Drupal\user\Entity\User;

/**
 * Default controller for the simple_node_importer module.
 */
class UserImportController extends ControllerBase {

  /**
   * Initiate Batch process for User Import.
   */
  public static function userImport($records, &$context) {
    $entity_type = 'user';
    foreach ($records as $record) {
      // Get user details if exists otherwse create.
      $batch_result['result'] = [];
      $user_data = [];

      if (empty($record['name'])) {
        $batch_result['result'] = $record;
      }

      $user = \Drupal::service('snp.get_services')->getUserByUsername($record['name']);

      if (!empty($record['mail'])) {
        $flag = \Drupal::service('email.validator')->isValid($record['mail']);
        $usermail_exist = \Drupal::service('snp.get_services')->getUserByEmail($record['mail'], 'content_validate');
        if ($usermail_exist != NULL || $flag == FALSE) {
          $batch_result['result'] = $record;
        }
      }

      if ($user) {
        $batch_result['result'] = $record;
      }

      $field_names = array_keys($record);

      $user_data = [
        'type' => 'user',
        'mail' => !empty($record['mail']) ? $record['mail'] : '',
        'name' => $record['name'],
        'status' => ($record['status'] == 1 || $record['status'] == TRUE) ? TRUE : FALSE,
        'roles' => !empty($record['roles']) ? $record['roles'] : 'authenticated',
      ];

      if (empty($batch_result['result'])) {
        $batch_result = \Drupal::service('snp.get_services')->checkFieldWidget($field_names, $record, $user_data, $entity_type);
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
        $user_data = $batch_result;
        $user_account = User::create($user_data);
        $user_account->save();
        $id = $user_account->id();

        if ($id) {
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
  public static function userImportBatchFinished($success, $results, $operations) {
    if ($success) {
      $rclink = Url::fromRoute('simple_node_importer.node_resolution_center', [], ['absolute' => TRUE]);
      $link = $rclink->toString();

      $created_count = !empty($results['created']) ? $results['created'] : NULL;
      $failed_count = !empty($results['failed']) ? $results['failed'] : NULL;

      if ($created_count && !$failed_count) {
        $import_status = new FormattableMarkup("Users registered successfully: @created_count", ['@created_count' => $created_count]);
      }
      elseif (!$created_count && $failed_count) {
        $import_status = new FormattableMarkup('Users import failed: @failed_count .To view failed records, please visit <a href="@link">Resolution Center</a>', ['@failed_count' => $failed_count, '@link' => $link]);
      }
      else {
        $import_status = new FormattableMarkup('Users registered successfully: @created_count<br/>Users import failed: @failed_count<br/>To view failed records, please visit <a href="@link">Resolution Center</a>',
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

      $statusMessage = t("Users import completed! Import status:<br/>@import_status", ['@import_status' => $import_status]);
      drupal_set_message($statusMessage);
    }
    else {
      $error_operation = reset($operations);
      $message = t('An error occurred while processing %error_operation with arguments: @arguments', [
        '%error_operation' => $error_operation[0],
        '@arguments' => print_r($error_operation[1], TRUE),
      ]);
      drupal_set_message($message, 'error');
    }

    return new RedirectResponse(\Drupal::url('<front>'));
  }

}
