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
  // Define a base table so we can create views from Fitbit API data.
  $data['fitbit_profile']['table']['group'] = t('Fitbit profile');
  $data['fitbit_profile']['table']['base'] = [
    'title' => t('Fitbit profile'),
    'query_id' => 'fitbit_profile',
    'help' => t('Fitbit profile query builder.'),
    'defaults' => [
      // Default to the display_name field.
      'field' => 'display_name',
    ],
  ];

  // Define all the fields we are exposing to views.
  $data['fitbit_profile']['display_name'] = [
    'title' => t('Display name'),
    'field' => [
      'id' => 'standard',
    ],
  ];

  return $data;
}
