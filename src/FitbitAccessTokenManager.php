<?php

namespace Drupal\fitbit;

use Drupal\Core\Database\Connection;

/**
 * CRUD operations for the fitbit_user_access_tokens table.
 */
class FitbitAccessTokenManager {

  /**
   * Database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

  /**
   * FitbitAccessTokenManager constructor.
   *
   * @param Connection $connection
   */
  public function __construct(Connection $connection) {
    $this->connection = $connection;
  }

  /**
   * Query for and access token for the given uid.
   *
   * @param int $uid
   *   User id for which to look up an access token.
   * @return array|null
   *   Returns an associative array of the access token details for the given
   *   uid if they exist, otherwise NULL.
   */
  public function get($uid) {
    $result = $this->connection->query('SELECT * FROM {fitbit_user_access_tokens} WHERE uid = :uid', [':uid' => $uid], ['fetch' => \PDO::FETCH_ASSOC]);
    foreach ($result as $row) {
      return $row;
    }
  }

  /**
   * Save access token details for the given uid.
   *
   * @param int $uid
   *   User id for which to save access token details.
   * @param array $data
   *   Associative array of access token details.
   */
  public function save($uid, $data) {
    $this->connection->merge('fitbit_user_access_tokens')
      ->key(['uid' => $uid])
      ->fields($data)
      ->execute();
  }
}
