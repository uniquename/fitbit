<?php

namespace Drupal\fitbit_views\Plugin\FitbitBaseTableEndpoint;

use Drupal\fitbit_views\FitbitBaseTableEndpointBase;
use Drupal\views\ResultRow;
use League\OAuth2\Client\Token\AccessToken;

/**
 * Fitbit Daily Activity Summary endpoint.
 *
 * @FitbitBaseTableEndpoint(
 *   id = "daily_activity_summary",
 *   name = @Translation("Fitbit daily activity summary"),
 *   description = @Translation("Retrives a summary and list of a user's activities and activity log entries for a given day."),
 *   response_key = "summary.steps"
 * )
 */
class DailyActivitySummary extends FitbitBaseTableEndpointBase {

  /**
   * {@inheritdoc}
   */
  public function getRowByAccessToken(AccessToken $access_token) {
    if ($data = $this->fitbitClient->getDailyActivitySummary($access_token)) {
      $data = $this->filterArrayByPath(array_keys($this->getFields()));
      return new ResultRow($data);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getFields() {
    $integer = ['id' => 'numeric'];
    $float = [
      'id' => 'numeric',
      'float' => TRUE,
    ];

    return [
      'goals.activeMinutes' => [
        'title' => $this->t('Goals - Active minutes'),
        'field' => $integer,
      ],
      'goals.caloriesOut' => [
        'title' => $this->t('Goals - Calories out'),
        'field' => $integer,
      ],
      'goals.distance' => [
        'title' => $this->t('Goals - Distance'),
        'field' => $float,
      ],
      'goals.steps' => [
        'title' => $this->t('Goals - Steps'),
        'field' => $integer,
      ],
      'summary.activeScore' => [
        'title' => $this->t('Active score'),
        'field' => $integer,
      ],
      'summary.activityCalories' => [
        'title' => $this->t('Activity calories'),
        'field' => $integer,
      ],
      'summary.caloriesBMR' => [
        'title' => $this->t('Calories BMR'),
        'field' => $integer,
      ],
      'summary.caloriesOut' => [
        'title' => $this->t('Calories out'),
        'field' => $integer,
      ],
      // @todo not sure what to do with summary.distances
      'summary.fairlyActiveMinutes' => [
        'title' => $this->t('Fairly active minutes'),
        'field' => $integer,
      ],
      'summary.lightlyActiveMinutes' => [
        'title' => $this->t('Lightly active minutes'),
        'field' => $integer,
      ],
      'summary.marginalCalories' => [
        'title' => $this->t('Marginal Calories'),
        'field' => $integer,
      ],
      'summary.sedentaryMinutes' => [
        'title' => $this->t('Sedentary minutes'),
        'field' => $integer,
      ],
      'summary.steps' => [
        'title' => $this->t('Steps'),
        'field' => $integer,
      ],
      'summary.veryActiveMinutes'=> [
        'title' => $this->t('Very active minutes'),
        'field' => $integer,
      ],
    ];
  }
}
