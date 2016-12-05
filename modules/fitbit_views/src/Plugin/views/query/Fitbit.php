<?php

namespace Drupal\fitbit_views\Plugin\views\query;

use Drupal\Core\Form\FormStateInterface;
use Drupal\fitbit\FitbitAccessTokenManager;
use Drupal\fitbit\FitbitClient;
use Drupal\fitbit_views\FitbitBaseTableEndpointInterface;
use Drupal\fitbit_views\FitbitBaseTableEndpointPluginManager;
use Drupal\views\Plugin\views\query\QueryPluginBase;
use Drupal\views\ResultRow;
use Drupal\views\ViewExecutable;
use Drupal\views\Views;
use Drupal\views\ViewsData;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Fitbit views query plugin which wraps calls to the Fitbit API in order to
 * expose the results to views.
 *
 * @ingroup views_query_plugins
 *
 * @ViewsQuery(
 *   id = "fitbit",
 *   title = @Translation("Fitbit"),
 *   help = @Translation("Query against the Fitbit API.")
 * )
 */
class Fitbit extends QueryPluginBase {

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
   * Fitbit base table endpoint plugin manager.
   *
   * @var \Drupal\fitbit_views\FitbitBaseTableEndpointPluginManager
   */
  protected $fitbitBaseTableEndpointPluginManager;

  /**
   * Fitbit constructor.
   *
   * @param array $configuration
   * @param string $plugin_id
   * @param mixed $plugin_definition
   * @param FitbitClient $fitbit_client
   * @param FitbitAccessTokenManager $fitbit_access_token_manager
   * @param FitbitBaseTableEndpointPluginManager $fitbit_base_table_endpoint_plugin_manager
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, FitbitClient $fitbit_client, FitbitAccessTokenManager $fitbit_access_token_manager, FitbitBaseTableEndpointPluginManager $fitbit_base_table_endpoint_plugin_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->fitbitClient = $fitbit_client;
    $this->fitbitAccessTokenManager = $fitbit_access_token_manager;
    $this->fitbitBaseTableEndpointPluginManager = $fitbit_base_table_endpoint_plugin_manager;
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
      $container->get('fitbit.access_token_manager'),
      $container->get('plugin.manager.fitbit_base_table_endpoints')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build(ViewExecutable $view) {
    // Mostly modeled off of \Drupal\views\Plugin\views\query\Sql::build()

    // Store the view in the object to be able to use it later.
    $this->view = $view;

    $view->initPager();

    // Let the pager modify the query to add limits.
    $view->pager->query();

    $view->build_info['query'] = $this->query();
    $view->build_info['count_query'] = $this->query(TRUE);
  }

  /**
   * {@inheritdoc}
   */
  public function query($get_count = FALSE) {
    // Fill up the $query array with properties that we will use in forming the
    // API request.
    $query = [];

    // Iterate over $this->where to gather up the filtering conditions to pass
    // along to the API. Note that views allows grouping of conditions, as well
    // as group operators. This does not apply to us, as I believe any filters
    // that work together automatically assume AND.
    if (isset($this->where)) {
      foreach ($this->where as $where_group => $where) {
        foreach ($where['conditions'] as $condition) {
          // Remove dot from beggining of the string.
          $field_name = ltrim($condition['field'], '.');
          $query[$field_name] = $condition['value'];
        }
      }
    }

    return $query;
  }

  /**
   * {@inheritdoc}
   */
  public function execute(ViewExecutable $view) {
    // Set the units according to the setting on the view.
    // @todo this probably belongs per endpoint somehow.
    if (!empty($this->options['accept_lang'])) {
      $this->fitbitClient->setAcceptLang($this->options['accept_lang']);
    }

    // Grab data regarding conditions placed on the query.
    $query = $view->build_info['query'];
    if ($access_tokens = $this->fitbitAccessTokenManager->loadMultipleAccessToken(empty($query['uid']) ? NULL : [$query['uid']])) {
      // Need the data about the table to know which endpoint to use.
      $views_data = Views::viewsData()->get($this->table);
      /** @var FitbitBaseTableEndpointInterface $fitbit_endpoint */
      $fitbit_endpoint = $this->fitbitBaseTableEndpointPluginManager->createInstance($views_data['table']['base']['fitbit_base_table_endpoint_id']);

      $index = 0;
      foreach ($access_tokens as $uid => $access_token) {
        if ($row = $fitbit_endpoint->getRowByAccessToken($access_token)) {
          $row->index = $index++;
          $row->uid = $uid;

          $view->result[] = $row;
        }
      }
    }
  }

  /**
   * Adds a simple condition to the query. Collect data on the configured filter
   * criteria so that we can appropriately apply it in the query() and execute()
   * methods.
   *
   * @param $group
   *   The WHERE group to add these to; groups are used to create AND/OR
   *   sections. Groups cannot be nested. Use 0 as the default group.
   *   If the group does not yet exist it will be created as an AND group.
   * @param $field
   *   The name of the field to check.
   * @param $value
   *   The value to test the field against. In most cases, this is a scalar. For more
   *   complex options, it is an array. The meaning of each element in the array is
   *   dependent on the $operator.
   * @param $operator
   *   The comparison operator, such as =, <, or >=. It also accepts more
   *   complex options such as IN, LIKE, LIKE BINARY, or BETWEEN. Defaults to =.
   *   If $field is a string you have to use 'formula' here.
   *
   * @see \Drupal\Core\Database\Query\ConditionInterface::condition()
   * @see \Drupal\Core\Database\Query\Condition
   */
  public function addWhere($group, $field, $value = NULL, $operator = NULL) {
    // Ensure all variants of 0 are actually 0. Thus '', 0 and NULL are all
    // the default group.
    if (empty($group)) {
      $group = 0;
    }

    // Check for a group.
    if (!isset($this->where[$group])) {
      $this->setWhereGroup('AND', $group);
    }

    $this->where[$group]['conditions'][] = array(
      'field' => $field,
      'value' => $value,
      'operator' => $operator,
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function defineOptions() {
    $options = parent::defineOptions();
    $options['accept_lang'] = array(
      'default' => NULL,
    );

    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state) {
    parent::buildOptionsForm($form, $form_state);

    $form['accept_lang'] = [
      '#type' => 'select',
      '#options' => $this->fitbitClient->getAcceptLangOptions(),
      '#title' => $this->t('Unit system'),
      '#default_value' => $this->options['accept_lang'],
      '#description' => $this->t('Set the unit system to use for FitbitAPI requests.'),
    ];
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
    return '';
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
