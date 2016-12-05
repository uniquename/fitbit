<?php

namespace Drupal\fitbit_views;

use Drupal\Component\Plugin\PluginInspectionInterface;
use League\OAuth2\Client\Token\AccessToken;
use Drupal\views\ResultRow;

/**
 * Defines an interface for Fitbit base table endpoint plugins.
 */
interface FitbitBaseTableEndpointInterface extends PluginInspectionInterface {

  /**
   * Make a request to a Fitbit endpoint using the given access token and return
   * a ResultRow object.
   *
   * @param \League\OAuth2\Client\Token\AccessToken $access_token
   *   Oauth access token object. Make the request on behalf of the user
   *   represented by the token.
   *
   * @return ResultRow|null
   */
  public function getRowByAccessToken(AccessToken $access_token);
}
