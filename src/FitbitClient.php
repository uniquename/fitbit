<?php

namespace Drupal\fitbit;

use djchen\OAuth2\Client\Provider\Fitbit;
use djchen\OAuth2\Client\Provider\FitbitUser;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
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
   * @return FitbitUser
   */
  public function getResourceOwner($uid) {
    if ($access_token = $this->getAccessTokenByUid($uid)) {
      return parent::getResourceOwner($access_token);
    }
  }

  /**
   * Trys to find a access token for the given $uid. Take care for refreshing
   * the token if necessary.
   *
   * @param int $uid
   *
   * @return AccessToken
   */
  protected function getAccessTokenByUid($uid) {
    if ($data = $this->fitbitAccessTokenManager->get($uid)) {

      $access_token = new AccessToken([
        'access_token' => $data['access_token'],
        'resource_owner_id' => $data['user_id'],
        'refresh_token' => $data['refresh_token'],
        'expires' => $data['expires'],
      ]);

      try {
        // Check if the access_token is expired. If it is, refresh it and save
        // it to the database.
        if ($access_token->hasExpired()) {
          $access_token = $this->getAccessToken('refresh_token', ['refresh_token' => $data['refresh_token']]);

          $this->fitbitAccessTokenManager->save($uid, [
            'access_token' => $access_token->getToken(),
            'expires' => $access_token->getExpires(),
            'refresh_token' => $access_token->getRefreshToken(),
            'user_id' => $access_token->getResourceOwnerId(),
          ]);
        }

        return $access_token;
      }
      catch (IdentityProviderException $e) {
        watchdog_exception('fitbit', $e);
      }
    }
  }
}
