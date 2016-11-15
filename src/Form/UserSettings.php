<?php

namespace Drupal\fitbit\Form;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Routing\TrustedRedirectResponse;
use Drupal\Core\Session\AccountInterface;
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
      $container->get('user.private_tempstore'),
      $container->get('database')
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
  public function buildForm(array $form, FormStateInterface $form_state, AccountInterface $user = NULL) {
    // Attempt to get the Fitibit account. If the account is properly linked,
    // this will return a result which we'll use to present some of the users
    // stats.
    if ($fitbit_user = $this->fitbitClient->getResourceOwner($user->id())) {
      $user_data = $fitbit_user->toArray();
      $form['authenticated'] = [
        '#markup' => t('<p>You\'re authenticated. Welcome @name.</p>', ['@name' => $fitbit_user->getDisplayName()]),
      ];
      if (!empty($user_data['avatar150'])) {
        $form['avatar'] = [
          '#theme' => 'image',
          '#uri' => $user_data['avatar150'],
        ];
      }
      if (!empty($user_data['averageDailySteps'])) {
        $form['avg_steps'] = [
          '#markup' => t('<p><strong>Average daily steps:</strong> @steps</p>', ['@steps' => $user_data['averageDailySteps']]),
        ];
      }
    }
    else {
      $form['submit'] = [
        '#type' => 'submit',
        '#value' => $this->t('Connect to Fitbit'),
      ];
    }

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
