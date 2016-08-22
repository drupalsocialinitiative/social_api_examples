<?php

namespace Drupal\social_post_twitter\Settings;

/**
 * Defines an interface for Social Post Twitter settings.
 */
interface TwitterPostSettingsInterface {

  /**
   * Gets the consumer key.
   *
   * @return string
   *   The consumer key.
   */
  public function getConsumerKey();

  /**
   * Gets the consumer secret.
   *
   * @return string
   *   The consumer secret.
   */
  public function getConsumerSecret();

}
