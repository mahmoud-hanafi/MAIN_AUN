<?php
/**
 * Implements hook_views_data_alter().
 * commerce_product Table name
 */
function gavias_hook_themer_views_data_alter(array &$data) {
    $data['commerce_product']['related_content_titles'] = array(
        'title' => t('Related Course titles'),
        'filter' => array(
            'title' => t('Related Course titles'),
            'help' => t('Specify a list of titles a Course node can have.'),
            'field' => 'product_id',
            'id' => 'gavias_hook_themer_related_content_titles'
        ),
    );
}