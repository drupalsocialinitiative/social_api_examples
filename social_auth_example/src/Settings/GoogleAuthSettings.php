<?php

namespace Drupal\social_auth_example\Settings;

use Drupal\social_api\Settings\SettingsBase;

/**
 * Returns the client information.
 *
 * This is the class defined in the settings handler of the Network Plugin
 * definition. The immutable configuration used by this class is also declared
 * in the definition.
 * @see \Drupal\social_auth_example\Plugin\Network\GoogleAuth.
 *
 * This should return the values required to request the social network. In this
 * case, Google requires a Client ID and a Client Secret.
 */
class GoogleAuthSettings extends SettingsBase implements GoogleAuthSettingsInterface {

  /**
   * Client ID.
   *
   * @var string
   */
  protected $clientId;

  /**
   * Client secret.
   *
   * @var string
   */
  protected $clientSecret;

  /**
   * {@inheritdoc}
   */
  public function getClientId() {
    if (!$this->clientId) {
      $this->clientId = $this->config->get('client_id');
    }
    return $this->clientId;
  }

  /**
   * {@inheritdoc}
   */
  public function getClientSecret() {
    if (!$this->clientSecret) {
      $this->clientSecret = $this->config->get('client_secret');
    }
    return $this->clientSecret;
  }

}
