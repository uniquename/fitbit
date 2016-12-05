<?php

namespace Drupal\fitbit_views\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines a fitbit base table endpoint annotation object.
 *
 * Plugin namespace: Plugin\fitbit\FitbitBaseTableEndpoint
 *
 * @see \Drupal\fitbit\FitbitBaseTableEndpointPluginManager
 * @see plugin_api
 *
 * @Annotation
 */
class FitbitBaseTableEndpoint extends Plugin {

  /**
   * The plugin ID.
   *
   * @var string
   */
  public $id;
}
