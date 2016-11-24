<?php

namespace Drupal\fitbit_views\Plugin\views\query;

use Drupal\views\Plugin\views\query\QueryPluginBase;

/**
 * Fitbit profile views query plugin which wraps calls to the Fitbit API's
 * profile resource in order to expose the results to views.
 *
 * @ingroup views_query_plugins
 *
 * @ViewsQuery(
 *   id = "fitbit_profile",
 *   title = @Translation("Fitbit profile"),
 *   help = @Translation("Query against the Fitbit API's profile resource.")
 * )
 */
class FitbitProfile extends QueryPluginBase {

}
