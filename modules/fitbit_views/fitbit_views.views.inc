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
  // @todo none of our fields are click sortable, using the default field plugins allows click sort. there may be other things like this.

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
      'id' => 'numeric',
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

  // Badges
  $data['fitbit_profile']['top_badges'] = [
    'title' => t('Top badges'),
    'field' => [
      'id' => 'fitbit_badges',
    ],
  ];

  // Avatar
  $data['fitbit_profile']['avatar'] = [
    'title' => t('Avatar'),
    'field' => [
      'id' => 'fitbit_avatar',
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
