<?php

namespace Drupal\fitbit\Controller;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Config\ImmutableConfig;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Database\Connection;
use Drupal\Core\Session\AccountInterface;
use Drupal\fitbit\FitbitClient;
use Drupal\user\PrivateTempStoreFactory;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\DependencyInjection\ContainerInterface;

class Authorization extends ControllerBase {

  /**
   * Fitbit client.
   *
   * @var \Drupal\fitbit\FitbitClient
   */
  protected $fitbitClient;

  /**
   * Session storage.
   *
   * @var \Drupal\user\PrivateTempStore
   */
  protected $tempStore;

  /**
   * Current request.
   *
   * @var \Symfony\Component\HttpFoundation\Request
   */
  protected $request;

  /**
   * Database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * Authorization constructor.
   *
   * @param FitbitClient $fitbit_client
   * @param PrivateTempStoreFactory $private_temp_store_factory
   * @param Request $request
   * @param Connection $connection
   * @param AccountInterface $current_user
   */
  public function __construct(FitbitClient $fitbit_client, PrivateTempStoreFactory $private_temp_store_factory, Request $request, Connection $connection, AccountInterface $current_user) {
    $this->fitbitClient = $fitbit_client;
    $this->tempStore = $private_temp_store_factory->get('fitbit');
    $this->request = $request;
    $this->connection = $connection;
    $this->currentUser = $current_user;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('fitbit.client'),
      $container->get('user.private_tempstore'),
      $container->get('request_stack')->getCurrentRequest(),
      $container->get('database'),
      $container->get('current_user')
    );
  }

  /**
   * Receive the authorization code from a Fitibit Authorization Code Flow
   * redirect, and request an access token from Fitbit.
   */
  public function authorize() {

    try {
      // Try to get an access token using the authorization code grant.
      $access_token = $this->fitbitClient->getAccessToken('authorization_code', [
        'code' => $this->request->get('code')]
      );

      // We have an access token, which we may use in authenticated
      // requests against the service provider's API.
      $this->connection->merge('fitbit_user_access_tokens')
        ->key(['uid' => $this->currentUser->id()])
        ->fields([
          'access_token' => $access_token->getToken(),
          'expires' => $access_token->getExpires(),
          'refresh_token' => $access_token->getRefreshToken(),
          'user_id' => $access_token->getResourceOwnerId(),
        ])
        ->execute();

      // Using the access token, we may look up details about the
      // resource owner.
      $resourceOwner = $this->fitbitClient->getResourceOwner($access_token);

      kint($resourceOwner->toArray());
    }
    catch (IdentityProviderException $e) {
      watchdog_exception('fitbit', $e);
    }

    return [
      '#markup' => 'You made it back, yay!',
    ];
  }

  /**
   * Check the state key from Fitbit to protect against CSRF.
   */
  public function checkAccess() {
    return AccessResult::allowedIf($this->tempStore->get('state') == $this->request->get('state'));
  }
}
