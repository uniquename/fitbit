<?php

namespace Drupal\fitbit;

use djchen\OAuth2\Client\Provider\Fitbit;
use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use League\OAuth2\Client\Token\AccessToken;

/**
 * Fitbit client wrapper. Implement custom methods to retrieve specific Fitbit
 * data using access_tokens stored in Drupal.
 */
class FitbitClient extends Fitbit {

  /**
   * Fitbit access token manager.
   *
   * @var \Drupal\fitbit\FitbitAccessTokenManager
   */
  protected $fitbitAccessTokenManager;

  /**
   * FitbitClient constructor.
   *
   * @param FitbitAccessTokenManager $fitbit_access_token_manager
   * @param array $options
   */
  public function __construct(FitbitAccessTokenManager $fitbit_access_token_manager, array $options = []) {
    parent::__construct($options);
    $this->fitbitAccessTokenManager = $fitbit_access_token_manager;
  }

  /**
   * Override getResourceOwner to allow callers to use a plain Drupal uid.
   *
   * @param int $uid
   *
   * @return ResourceOwnerInterface
   */
  public function getResourceOwner($uid) {
    if ($data = $this->fitbitAccessTokenManager->get($uid)) {

      $access_token = new AccessToken([
        'access_token' => $data['access_token'],
        'resource_owner_id' => $data['user_id'],
        'refresh_token' => $data['refresh_token'],
        'expires' => $data['expires'],
      ]);

      return parent::getResourceOwner($access_token);
    }
  }
}
