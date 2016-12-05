<?php
/**
 * @file
 *
 * Views related hooks.
 */

/**
 * Implements hook_views_data().
 */
function fitbit_views_views_data() {
  /** @var \Drupal\fitbit_views\FitbitBaseTableEndpointPluginManager $fitbit_base_table_endpoint_manager */
  $fitbit_base_table_endpoint_manager = \Drupal::service('plugin.manager.fitbit_base_table_endpoints');
  foreach ($fitbit_base_table_endpoint_manager->getDefinitions() as $plugin_id => $plugin_definition) {
    /** @var \Drupal\fitbit_views\FitbitBaseTableEndpointInterface $base_table_endpoint */
    $base_table_endpoint = $fitbit_base_table_endpoint_manager->createInstance($plugin_id);
    $data['fitbit_' . $plugin_id]['table']['group'] = $base_table_endpoint->getName();
    $data['fitbit_' . $plugin_id]['table']['base'] = [
      'title' => $base_table_endpoint->getName(),
      'query_id' => 'fitbit',
      'fitbit_base_table_endpoint_id' => $plugin_id,
      'help' => $base_table_endpoint->getDescription(),
      'defaults' => [
        'field' => $base_table_endpoint->getResponseKey(),
      ],
    ];

    // Tack on all the field definitions.
    foreach ($base_table_endpoint->getFields() as $key => $field) {
      if ($field) {
        $data['fitbit_' . $plugin_id][$key] = $field;
      }
    }

    // Tack on the default uid
    // Filter by Drupal uid
    $data['fitbit_' . $plugin_id]['uid'] = [
      'title' => t('User id'),
      'help' => t('Drupal user id, not to be confused with Fitbit profile id.'),
      'field' => [
        'id' => 'standard',
      ],
      'filter' => [
        'id' => 'fitbit_uid',
      ],
    ];
  }

  return $data;
}
