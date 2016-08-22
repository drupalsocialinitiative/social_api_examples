<?php

namespace Drupal\social_post_twitter;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Manages storage of Twitter User entities.
 */
class TwitterUserEntityManager {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityManager;

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * TwitterUserEntityManager constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_manager
   *   The entity type manager.
   * @param \Drupal\Core\Session\AccountInterface $current_user
   *   The current user.
   */
  public function __construct(EntityTypeManagerInterface $entity_manager, AccountInterface $current_user) {
    $this->entityManager = $entity_manager;
    $this->currentUser = $current_user;
  }

  /**
   * Saves the user in the database as a Twitter user.
   *
   * @param array $access_token
   *   Array with long live tokens returned by Twitter.
   *
   * @return int
   *   The current drupal user id.
   */
  public function saveUser(array &$access_token) {

    $entity = $this->entityManager->getStorage('social_post_twitter_user');

    // Checks if the user has already granted permissions.
    $user = $entity->loadByProperties([
      'twitter_id' => $access_token['user_id'],
      'uid' => (int) $this->currentUser->id(),
    ]);

    if (!count($user) > 0) {
      $twitter_user = array(
        'twitter_id' => $access_token['user_id'],
        'screen_name' => $access_token['screen_name'],
        'token' => $access_token['oauth_token'],
        'token_secret' => $access_token['oauth_token_secret'],
        'uid' => (int) $this->currentUser->id(),
      );

      $entity->create($twitter_user)->save();

      drupal_set_message('Twitter account was successfully registered');
    }
    else {
      drupal_set_message('This user has already granted permission for the twitter account', 'warning');
    }

    return $this->currentUser->id();
  }

}
