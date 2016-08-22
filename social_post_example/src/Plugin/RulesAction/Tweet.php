<?php

namespace Drupal\social_post_twitter\Plugin\RulesAction;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\rules\Core\RulesActionBase;
use Drupal\social_post_twitter\Plugin\Network\TwitterPost;
use Drupal\social_post_twitter\Plugin\Network\TwitterPostInterface;
use Drupal\social_post_twitter\TwitterPostTokenManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a 'Tweet' action.
 *
 * @RulesAction(
 *   id = "social_post_twitter_tweet",
 *   label = @Translation("Tweet"),
 *   category = @Translation("Social Post"),
 *   context = {
 *     "status" = @ContextDefinition("string",
 *       label = @Translation("Tweet content"),
 *       description = @Translation("You can include tokens like !node:url")
 *     )
 *   }
 * )
 */
class Tweet extends RulesActionBase implements ContainerFactoryPluginInterface {

  /**
   * The twitter post network plugin.
   *
   * @var \Drupal\social_post_twitter\Plugin\Network\TwitterPostInterface
   */
  protected $twitterPost;

  /**
   * The social post twitter entity storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface;
   */
  protected $twitterEntity;

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * The Twitter post token manager.
   *
   * @var \Drupal\social_post_twitter\TwitterPostTokenManager
   */
  protected $tokenManager;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    /* @var TwitterPost $twitter_post*/
    $twitter_post = $container->get('plugin.network.manager')->createInstance('social_post_twitter');

    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $twitter_post,
      $container->get('entity_type.manager'),
      $container->get('current_user'),
      $container->get('twitter_post.token_manager')
    );
  }

  /**
   * Tweet constructor.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin ID for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\social_post_twitter\Plugin\Network\TwitterPostInterface $twitter_post
   *   The twitter post network plugin.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_manager
   *   The entity type manager.
   * @param \Drupal\Core\Session\AccountInterface $current_user
   *   The current user.
   * @param \Drupal\social_post_twitter\TwitterPostTokenManager $token_manager
   *   The Twitter post token manager.
   */
  public function __construct(array $configuration,
                              $plugin_id,
                              $plugin_definition,
                              TwitterPostInterface $twitter_post,
                              EntityTypeManagerInterface $entity_manager,
                              AccountInterface $current_user,
                              TwitterPostTokenManager $token_manager) {

    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->twitterPost = $twitter_post;
    $this->twitterEntity = $entity_manager->getStorage('social_post_twitter_user');
    $this->currentUser = $current_user;
    $this->tokenManager = $token_manager;
  }

  /**
   * Executes the action with the given context.
   *
   * @param string $status
   *   The tweet text.
   */
  protected function doExecute($status) {
    $status = $this->tokenManager->formatStatus($status);

    $accounts = $this->getTwitterAccountsByUserId($this->currentUser->id());
    /* @var \Drupal\social_post_twitter\Entity\TwitterUserInterface $account */
    foreach ($accounts as $account) {
      $this->twitterPost->doPost($account->getAccessToken(), $account->getAccessTokenSecret(), $status);
    }
  }

  /**
   * Gets all the accounts associated to a user id.
   *
   * @param int $user_id
   *   The user id.
   *
   * @return \Drupal\Core\Entity\EntityInterface[]
   *   Array with the accounts.
   */
  protected function getTwitterAccountsByUserId($user_id) {
    $accounts = $this->twitterEntity->loadByProperties([
      'uid' => $user_id,
    ]);

    return $accounts;
  }

}
