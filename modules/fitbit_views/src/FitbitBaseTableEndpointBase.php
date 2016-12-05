<?php

namespace Drupal\fitbit_views;

use Drupal\Component\Plugin\PluginBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\fitbit\FitbitClient;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Base class for Fitbit base table endpoint plugins.
 */
abstract class FitbitBaseTableEndpointBase extends PluginBase implements FitbitBaseTableEndpointInterface, ContainerFactoryPluginInterface  {

  /**
   * Fitbit client.
   *
   * @var \Drupal\fitbit\FitbitClient
   */
  protected $fitbitClient;

  /**
   * All endpoints will require a FitbitClient to do their work, save them all
   * from having to get the serivice from the container.
   *
   * @param array $configuration
   * @param string $plugin_id
   * @param mixed $plugin_definition
   * @param FitbitClient $fitbit_client
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, FitbitClient $fitbit_client) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->fitbitClient = $fitbit_client;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('fitbit.client')
    );
  }
}
