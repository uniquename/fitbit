<?php

namespace Drupal\fitbit_views\Plugin\views\query;

use Drupal\fitbit\FitbitAccessTokenManager;
use Drupal\fitbit\FitbitClient;
use Drupal\views\Plugin\views\join\JoinPluginBase;
use Drupal\views\Plugin\views\query\QueryPluginBase;
use Drupal\views\ResultRow;
use Drupal\views\ViewExecutable;
use Symfony\Component\DependencyInjection\ContainerInterface;

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

  /**
   * Fitbit client.
   *
   * @var \Drupal\fitbit\FitbitClient
   */
  protected $fitbitClient;

  /**
   * Fitbit access token manager for loading access tokens from the database.
   *
   * @var \Drupal\fitbit\FitbitAccessTokenManager
   */
  protected $fitbitAccessTokenManager;

  /**
   * FitbitProfile constructor.
   *
   * @param array $configuration
   * @param string $plugin_id
   * @param mixed $plugin_definition
   * @param FitbitClient $fitbit_client
   * @param FitbitAccessTokenManager $fitbit_access_token_manager
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, FitbitClient $fitbit_client, FitbitAccessTokenManager $fitbit_access_token_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->fitbitClient = $fitbit_client;
    $this->fitbitAccessTokenManager = $fitbit_access_token_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('fitbit.client'),
      $container->get('fitbit.access_token_manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function execute(ViewExecutable $view) {
    $access_tokens = $this->fitbitAccessTokenManager->loadMultipleAccessToken();
    $index = 0;
    foreach ($access_tokens as $access_token) {
      if ($fitbit_user = $this->fitbitClient->getResourceOwner($access_token)) {
        $user_data = $fitbit_user->toArray();
        // The index key is very important. Views uses this to look up values
        // for each row. Without it, views won't show any of your result rows.
        $row['index'] = $index++;
        $row['display_name'] = $user_data['displayName'];
        $view->result[] = new ResultRow($row);
      }
    }
  }

  /**
   * The following methods replicate the interface of Views' default SQL query
   * plugin backend to simplify the Views integration of the Fitbit
   * API. It's necessary to define these, since many handlers assume they are
   * working against a SQL query plugin backend. There is an issue that details
   * this lack of an enforced contract as a bug
   * (https://www.drupal.org/node/2484565). Sigh.
   *
   * @see https://www.drupal.org/node/2484565
   */

  /**
   * Ensures a table exists in the query.
   *
   * This replicates the interface of Views' default SQL backend to simplify
   * the Views integration of the Fitbit API. Since the Fitbit API has no
   * concept of "tables", this method implementation does nothing. If you are
   * writing Fitbit API-specific Views code, there is therefore no reason at all
   * to call this method.
   * See https://www.drupal.org/node/2484565 for more information.
   *
   * @return string
   *   An empty string.
   */
  public function ensureTable($table, $relationship = NULL) {
    return $table;
  }

  /**
   * Adds a field to the table. In our case, the Fitibt API has no
   * notion of limiting the fields that come back, so tracking a list
   * of fields to fetch is irrellevant for us. Hence this function body is more
   * or less empty and it serves only to satisfy handlers that may assume an
   * addField method is present b/c they were written against Views' default SQL
   * backend.
   *
   * This replicates the interface of Views' default SQL backend to simplify
   * the Views integration of the Fitbit API.
   *
   * @param string $table
   *   NULL in most cases, we could probably remove this altogether.
   * @param string $field
   *   The name of the metric/dimension/field to add.
   * @param string $alias
   *   Probably could get rid of this too.
   * @param array $params
   *   Probably could get rid of this too.
   *
   * @return string
   *   The name that this field can be referred to as.
   *
   * @see \Drupal\views\Plugin\views\query\Sql::addField()
   */
  public function addField($table, $field, $alias = '', $params = array()) {
    return $field;
  }

  /**
   * End of methods necessary to replicate the interface of Views' default SQL
   * query plugin backend to simplify the Views integration of the Fitbit API.
   */
}
