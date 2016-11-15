<?php

namespace Drupal\fitbit\Form;

use djchen\OAuth2\Client\Provider\Fitbit;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Config\ImmutableConfig;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Routing\TrustedRedirectResponse;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Url;
use Drupal\fitbit\FitbitClient;
use Drupal\user\PrivateTempStoreFactory;
use Drupal\user\UserInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class UserSettings extends FormBase {

  /**
   * Fitbit client.
   *
   * @var \Drupal\fitbit\FitbitClient
   */
  protected $fitbitClient;

  /**
   * Current logged in user.
   *
   * @var AccountInterface
   */
  protected $currentUser;

  /**
   * Session storage.
   *
   * @var \Drupal\user\PrivateTempStoreFactory
   */
  protected $tempStore;

  /**
   * UserSettings constructor.
   *
   * @param FitbitClient $fitbit_client
   * @param PrivateTempStoreFactory $private_temp_store_factory
   */
  public function __construct(FitbitClient $fitbit_client, PrivateTempStoreFactory $private_temp_store_factory) {
    $this->fitbitClient = $fitbit_client;
    $this->tempStore = $private_temp_store_factory->get('fitbit');
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('fitbit.client'),
      $container->get('user.private_tempstore')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'fitbit_user_settings';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Connect to Fitbit'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $authorization_url = $this->fitbitClient->getAuthorizationUrl();
    $this->tempStore->set('state', $this->fitbitClient->getState());
    $form_state->setResponse(new TrustedRedirectResponse($authorization_url, 302));
  }

  /**
   * Checks access for a users Fitbit settings page.
   *
   * @param AccountInterface $account
   *   Current user.
   * @param UserInterface $user
   *   User being accessed.
   *
   * @return AccessResult
   */
  public function checkAccess(AccountInterface $account, UserInterface $user = NULL) {
    // Only allow access if user has authorize fitbit account and it's their
    // own page.
    return AccessResult::allowedIf($account->hasPermission('authorize fitbit account') && $account->id() === $user->id());
  }
}
