<?php

namespace Drupal\social_post_twitter\Settings;

use Drupal\social_api\Settings\SettingsBase;

/**
 * Returns the app information.
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
