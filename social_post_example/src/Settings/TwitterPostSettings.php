<?php

namespace Drupal\social_post_example\Settings;

use Drupal\social_api\Settings\SettingsBase;

/**
 * Returns the app information.
 *
 * This is the class defined in the settings handler of the Network Plugin
 * definition. The immutable configuration used by this class is also declared
 * in the definition.
 *
 * @see \Drupal\social_post_example\Plugin\Network\TwitterPost
 *
 * This should return the values required to request the social network. In this
 * case, Twitter requires a Consumer Key and a Consumer Secret.
 */
class TwitterPostSettings extends SettingsBase implements TwitterPostSettingsInterface {

  /**
   * Consumer key.
   *
   * @var string
   */
  protected $consumerKey;

  /**
   * Consumer secret.
   *
   * @var string
   */
  protected $consumerSecret;

  /**
   * {@inheritdoc}
   */
  public function getConsumerKey() {
    if (!$this->consumerKey) {
      $this->consumerKey = $this->config->get('consumer_key');
    }

    return $this->consumerKey;
  }

  /**
   * {@inheritdoc}
   */
  public function getConsumerSecret() {
    if (!$this->consumerSecret) {
      $this->consumerSecret = $this->config->get('consumer_secret');
    }

    return $this->consumerSecret;
  }

}
