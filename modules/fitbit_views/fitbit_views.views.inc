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

  // Display name.
  $data['fitbit_profile']['display_name'] = [
    'title' => t('Display name'),
    'field' => [
      'id' => 'standard',
    ],
  ];

  // Average daily steps
  $data['fitbit_profile']['average_daily_steps'] = [
    'title' => t('Average daily steps'),
    'field' => [
      'id' => 'standard',
    ],
  ];

  // Weight
  $data['fitbit_profile']['weight'] = [
    'title' => t('Weight'),
    'field' => [
      'id' => 'numeric',
      'float' => TRUE,
    ],
  ];

  // Height
  $data['fitbit_profile']['height'] = [
    'title' => t('Height'),
    'field' => [
      'id' => 'numeric',
      'float' => TRUE,
    ],
  ];

  // Filter by Drupal uid
  $data['fitbit_profile']['uid'] = [
    'title' => t('User id'),
    'help' => t('Drupal user id, not to be confused with Fitbit profile id.'),
    'field' => [
      'id' => 'standard',
    ],
    'filter' => [
      'id' => 'fitbit_uid',
    ],
  ];

  return $data;
}
