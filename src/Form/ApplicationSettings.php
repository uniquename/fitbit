<?php

namespace Drupal\fitbit\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class ApplicationSettings.
 *
 * @package Drupal\fitbit\Form
 */
class ApplicationSettings extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'fitbit_application_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['fitbit.application_settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('fitbit.application_settings');

    $form['client_id'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Client ID'),
      '#description' => $this->t('Enter the OAuth 2.0 Client ID from your Fitbit application settings.'),
      '#default_value' => $config->get('client_id'),
    );
    $form['client_secret'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Client secret'),
      '#description' => $this->t('Enter the Client Secret from your Fitbit application settings.'),
      '#default_value' => $config->get('client_secret'),
    );

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('fitbit.application_settings')
      ->set('client_id', $form_state->getValue('client_id'))
      ->set('client_secret', $form_state->getValue('client_secret'))
      ->save();

    parent::submitForm($form, $form_state);
  }
}
