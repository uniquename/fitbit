<?php

namespace Drupal\fitbit_views\Plugin\FitbitBaseTableEndpoint;

use Drupal\fitbit_views\FitbitBaseTableEndpointBase;
use Drupal\views\ResultRow;
use League\OAuth2\Client\Token\AccessToken;

/**
 * Fitbit profile endpoint.
 *
 * @FitbitBaseTableEndpoint("profile")
 */
class Profile extends FitbitBaseTableEndpointBase {

  /**
   * {@inheritdoc}
   */
  public function getRowByAccessToken(AccessToken $access_token) {
    if ($fitbit_user = $this->fitbitClient->getResourceOwner($access_token)) {
      $user_data = $fitbit_user->toArray();

      // The index key is very important. Views uses this to look up values
      // for each row. Without it, views won't show any of your result rows.
      $row['display_name'] = $user_data['displayName'];
      $row['average_daily_steps'] = $user_data['averageDailySteps'];
      $row['weight'] = $user_data['weight'];
      $row['height'] = $user_data['height'];
      $row['top_badges'] = $user_data['topBadges'];
      $row['avatar'] = [
        'avatar' => $user_data['avatar'],
        'avatar150' => $user_data['avatar150'],
      ];
      return new ResultRow($row);
    }
  }
}