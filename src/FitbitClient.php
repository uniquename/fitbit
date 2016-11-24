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
   * Get the resource owner by Drupal uid.
   *
   * @param AccessToken $access_token
   *   Fitbit AccessToken object.
   *
   * @return FitbitUser
   */
  public function getResourceOwner(AccessToken $access_token) {
    try {
      return parent::getResourceOwner($access_token);
    }
    catch (IdentityProviderException $e) {
      watchdog_exception('fitbit', $e);
    }
  }

  /**
   * Get a users badges.
   *
   * @param AccessToken $access_token
   *   Fitbit AccessToken object.
   *
   * @return mixed
   */
  public function getBadges(AccessToken $access_token) {
    return $this->request('/1/user/-/badges.json', $access_token);
  }

  /**
   * Get daily activity for the given user.
   *
   * @param AccessToken $access_token
   *   Fitbit AccessToken object.
   * @param string $date
   *
   * @return mixed
   */
  public function getDailyActivitySummary(AccessToken $access_token, $date = NULL) {
    if (!isset($date)) {
      $date = date('Y-m-d', REQUEST_TIME);
    }
    return $this->request('/1/user/-/activities/date/' . $date . '.json', $access_token);
  }

  /**
   * Get activity time series.
   *
   * @param AccessToken $access_token
   *   Fitbit AccessToken object.
   * @param $resource_path
   *   One of the allowable resource paths accepted by the Fitbit API, for
   *   example, activities/steps. For the full list, see
   *   https://dev.fitbit.com/docs/activity/#resource-path-options
   * @param string $date
   *   The end date of the period specified in the format yyyy-MM-dd or today.
   * @param string $period
   *   The range for which data will be returned. Options are 1d, 7d, 30d, 1w,
   *   1m, 3m, 6m, 1y.
   *
   * @return mixed
   */
  public function getActivityTimeSeries(AccessToken $access_token, $resource_path, $date = NULL, $period = NULL) {
    isset($date) ?: $date = 'today';
    isset($period) ?: $period = '7d';
    return $this->request('/1/user/-/' . $resource_path . '/date/' . $date . '/' . $period . '.json', $access_token);
  }

  /**
   * Request a resource on the Fitbit API.
   *
   * @param string $resource
   *   Path to the resource on the API. Should include a leading /.
   * @param AccessToken $access_token
   *   Fitbit AccessToken object.
   *
   * @return mixed
   *   API response.
   */
  public function request($resource, AccessToken $access_token) {
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
