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
   * Get the resource owner by Drupal uid.
   *
   * @param int $uid
   *
   * @return FitbitUser
   */
  public function getResourceOwnerByUid($uid) {
    if ($access_token = $this->getAccessTokenByUid($uid)) {
      return parent::getResourceOwner($access_token);
    }
  }

  /**
   * Get a users badges.
   *
   * @param int $uid
   *
   * @return mixed
   */
  public function getBadges($uid) {
    return $this->request('/1/user/-/badges.json', $uid);
  }

  /**
   * Get daily activity for the given user.
   *
   * @param int $uid
   * @param string $date
   *
   * @return mixed
   */
  public function getDailyActivitySummary($uid, $date = NULL) {
    if (!isset($date)) {
      $date = date('Y-m-d', REQUEST_TIME);
    }
    return $this->request('/1/user/-/activities/date/' . $date . '.json', $uid);
  }

  public function getActivityTimeSeries($uid, $resource, $date = NULL, $period = NULL) {
    if (!isset($date)) {
      $date = date('Y-m-d', REQUEST_TIME);
    }
    if (!isset($period)) {
      $period = '7d';
    }
    return $this->request('/1/user/-/' . $resource . '/date/' . $date . '/' . $period . '.json', $uid);
  }

  /**
   * Request a resource on the Fitbit API.
   *
   * @param string $resource
   *   Path to the resource on the API. Should include a leading /.
   * @param int $uid
   *   Drupal user id.
   *
   * @return mixed
   *   API response.
   */
  protected function request($resource, $uid) {
    if ($access_token = $this->getAccessTokenByUid($uid)) {
      $request = $this->getAuthenticatedRequest(
        Fitbit::METHOD_GET,
        Fitbit::BASE_FITBIT_API_URL . $resource,
        $access_token,
        // @todo setting for units
        ['headers' => [Fitbit::HEADER_ACCEPT_LANG => 'en_US']]
      );

      try {
        return $this->getResponse($request);
      }
      catch (IdentityProviderException $e) {
        watchdog_exception('fitbit', $e);
      }
    }
  }

  /**
   * Get the access token by Drupal uid. Take care for refreshing
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
