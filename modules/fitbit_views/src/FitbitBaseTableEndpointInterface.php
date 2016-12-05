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
   * Get the name of the plugin.
   *
   * @return string
   *   The name of the plugin.
   */
  public function getName();

  /**
   * Get the description of the plugin.
   *
   * @return string
   *   The description of the plugin.
   */
  public function getDescription();

  /**
   * Get the name of a string key which is always present in the response.
   *
   * @return string
   *   Name of a string key that is always in the response in dot path notation.
   */
  public function getResponseKey();

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

  /**
   * Inform views about the fields this endpoint exposes.
   *
   * @return array
   *   Associative array. Keys should be in dot notation representation of the
   *   value to pick out of the API response. Values are an associative array
   *   appropriate to pass along to views in a hook_views_data implementation
   *   as the definition of a field.
   */
  public function getFields();
}
