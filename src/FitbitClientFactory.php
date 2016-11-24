<?php

namespace Drupal\fitbit;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Url;

class FitbitClientFactory {

  /**
   * Create an instance of FitbitClient.
   *
   * @param ConfigFactoryInterface $config_factory
   *
   * @return FitbitClient
   */
  public static function create(ConfigFactoryInterface $config_factory) {
    $config = $config_factory->get('fitbit.application_settings');
    $options = [
      'clientId' => $config->get('client_id'),
      'clientSecret' => $config->get('client_secret'),
      'redirectUri' => Url::fromRoute('fitbit.authorization', [], ['absolute' => TRUE])->toString(),
    ];
    return new FitbitClient($options);
  }
}
