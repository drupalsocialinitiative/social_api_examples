<?php

namespace Drupal\social_auth_example\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\social_auth\Form\SocialAuthSettingsForm;

/**
 * Settings form for Social Auth Example.
 */
class ExampleAuthSettingsForm extends SocialAuthSettingsForm {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return array_merge(['social_auth_example.settings'], parent::getEditableConfigNames());
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'social_auth_example_settings';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('social_auth_example.settings');

    $form['example_settings'] = [
      '#type' => 'details',
      '#title' => $this->t('Google Client settings'),
      '#open' => TRUE,
      '#description' => $this->t('You need to first create a Google App at <a href="@google-dev">@google-dev</a>',
        ['@google-dev' => 'https://console.developers.google.com']),
    ];

    $form['example_settings']['client_id'] = [
      '#type' => 'textfield',
      '#required' => TRUE,
      '#title' => $this->t('Client ID'),
      '#default_value' => $config->get('client_id'),
      '#description' => $this->t('Copy the Client ID here'),
    ];

    $form['example_settings']['client_secret'] = [
      '#type' => 'textfield',
      '#required' => TRUE,
      '#title' => $this->t('Client Secret'),
      '#default_value' => $config->get('client_secret'),
      '#description' => $this->t('Copy the Client Secret here'),
    ];

    $form['example_settings']['authorized_redirect_url'] = [
      '#type' => 'textfield',
      '#disabled' => TRUE,
      '#title' => $this->t('Authorized redirect URIs'),
      '#description' => $this->t('Copy this value to <em>Authorized redirect URIs</em> field of your Google App settings.'),
      '#default_value' => Url::fromRoute('social_auth_example.callback')->setAbsolute()->toString(),
    ];

    $form['example_settings']['advanced'] = [
      '#type' => 'details',
      '#title' => $this->t('Advanced settings'),
      '#open' => FALSE,
    ];

    $form['example_settings']['advanced']['scopes'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Scopes for API call'),
      '#default_value' => $config->get('scopes'),
      '#description' => $this->t('Define any additional scopes to be requested, separated by a comma (e.g.: https://www.googleapis.com/auth/youtube.upload,https://www.googleapis.com/auth/youtube.readonly).<br>
                                  The scopes  \'openid\' \'email\' and \'profile\' are added by default and always requested.<br>
                                  You can see the full list of valid scopes and their description <a href="@scopes">here</a>.', ['@scopes' => 'https://developers.google.com/apis-explorer/#p/']),
    ];

    $form['example_settings']['advanced']['endpoints'] = [
      '#type' => 'textarea',
      '#title' => $this->t('API calls to be made to collect data'),
      '#default_value' => $config->get('endpoints'),
      '#description' => $this->t('Define the Endpoints to be requested when user authenticates with Google for the first time<br>
                                  Enter each endpoint in different lines in the format <em>endpoint</em>|<em>name_of_endpoint</em>.<br>
                                  <b>For instance:</b><br>
                                  /youtube/v3/playlists?maxResults=2&mine=true&part=snippet|playlists_list<br>'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();

    $this->config('social_auth_example.settings')
      ->set('client_id', $values['client_id'])
      ->set('client_secret', $values['client_secret'])
      ->set('scopes', $values['scopes'])
      ->set('endpoints', $values['endpoints'])
      ->save();

    parent::submitForm($form, $form_state);
  }

}
