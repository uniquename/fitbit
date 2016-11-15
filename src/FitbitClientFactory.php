<?php

namespace Drupal\fitbit;


use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\Url;

class FitbitClientFactory {

  /**
   * Create an instance of FitbitClient.
   *
   * @param ConfigFactoryInterface $config_factory
   * @param FitbitAccessTokenManager $fitbit_access_token_manager
   *
   * @return FitbitClient
   */
  public static function create(ConfigFactoryInterface $config_factory, FitbitAccessTokenManager $fitbit_access_token_manager) {
    $config = $config_factory->get('fitbit.application_settings');
    $options = [
      'clientId' => $config->get('client_id'),
      'clientSecret' => $config->get('client_secret'),
      'redirectUri' => Url::fromRoute('fitbit.authorization', [], ['absolute' => TRUE])->toString(),
    ];
    return new FitbitClient($fitbit_access_token_manager, $options);
  }
}
