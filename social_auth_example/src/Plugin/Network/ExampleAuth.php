<?php

namespace Drupal\social_auth_example\Plugin\Network;

use Drupal\Core\Url;
use Drupal\social_api\SocialApiException;
use Drupal\social_auth\Plugin\Network\NetworkBase;
use Drupal\social_auth_example\Settings\ExampleAuthSettings;
use League\OAuth2\Client\Provider\Google;

/**
 * Defines Social Auth Google Network Plugin.
 *
 * This is the main definition of the Network Plugin. The most important
 * properties are listed below.
 *
 * id: The unique identifier of this Network Plugin. It must have the same name
 * as the module itself.
 *
 * social_network: The Social Network for which this Network Plugin is defined.
 *
 * type: The type of the Network Plugin:
 * - social_auth: A Network Plugin for user login/registration.
 * - social_post: A Network Plugin for autoposting tasks.
 * - social_widgets: A Network Plugin for social networks' widgets.
 *
 * handlers: Defined the settings manager and the configuration identifier
 * in the configuration manager. In detail:
 *
 * - settings: The settings management for this Network Plugin.
 *   - class: The class for getting the configuration data. The settings
 *     property of this class is the instance of the class declared in this
 *     field.
 *   - config_id: The configuration id. It usually is the same used by the
 *     configuration form.
 *
 * @see \Drupal\social_auth_example\Form\ExampleAuthSettingsForm
 *
 * @Network(
 *   id = "social_auth_example",
 *   social_network = "Example",
 *   type = "social_auth",
 *   handlers = {
 *      "settings": {
 *          "class": "\Drupal\social_auth_example\Settings\ExampleAuthSettings",
 *          "config_id": "social_auth_example.settings"
 *      }
 *   }
 * )
 */
class ExampleAuth extends NetworkBase {

  /**
   * {@inheritdoc}
   *
   * Initializes the Google SDK to request Google Accounts.
   *
   * The returning value of this method is what is returned when an instance of
   * this Network Plugin called the getSdk method.
   *
   * @see \Drupal\social_auth_example\Controller\ExampleAuthController::callback
   * @see \Drupal\social_auth\Controller\OAuth2ControllerBase::processCallback
   */
  public function initSdk() {
    $class_name = '\League\OAuth2\Client\Provider\Google';
    if (!class_exists($class_name)) {
      throw new SocialApiException(sprintf('The Google library for PHP League OAuth2 not found. Class: %s.', $class_name));
    }

    /* @var \Drupal\social_auth_example\Settings\ExampleAuthSettings $settings */
    $settings = $this->settings;

    if ($this->validateConfig($settings)) {
      // All these settings are mandatory.
      $league_settings = [
        'clientId' => $settings->getClientId(),
        'clientSecret' => $settings->getClientSecret(),
        'redirectUri' => Url::fromRoute('social_auth_example.callback')->setAbsolute()->toString(),
        'accessType' => 'offline',
        'verify' => FALSE,
      ];

      // Proxy configuration data for outward proxy.
      $proxyUrl = $this->siteSettings->get('http_client_config')['proxy']['http'];
      if ($proxyUrl) {
        $league_settings['proxy'] = $proxyUrl;
      }

      return new Google($league_settings);
    }

    return FALSE;
  }

  /**
   * Checks that module is configured.
   *
   * @param \Drupal\social_auth_example\Settings\ExampleAuthSettings $settings
   *   The implementer authentication settings.
   *
   * @return bool
   *   True if module is configured.
   *   False otherwise.
   */
  protected function validateConfig(ExampleAuthSettings $settings) {
    $client_id = $settings->getClientId();
    $client_secret = $settings->getClientSecret();
    if (!$client_id || !$client_secret) {
      $this->loggerFactory
        ->get('social_auth_example')
        ->error('Define Client ID and Client Secret on module settings.');

      return FALSE;
    }

    return TRUE;
  }

}
