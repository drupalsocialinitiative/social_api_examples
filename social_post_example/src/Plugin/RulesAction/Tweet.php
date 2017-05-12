<?php

namespace Drupal\social_post_example\Plugin\RulesAction;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\rules\Core\RulesActionBase;
use Drupal\social_post_example\Plugin\Network\TwitterPostInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a 'Tweet' action.
 *
 * @RulesAction(
 *   id = "social_post_example_tweet",
 *   label = @Translation("Tweet"),
 *   category = @Translation("Social Post"),
 *   context = {
 *     "status" = @ContextDefinition("string",
 *       label = @Translation("Tweet content"),
 *       description = @Translation("Specifies the status to post.")
 *     )
 *   }
 * )
 */
class Tweet extends RulesActionBase implements ContainerFactoryPluginInterface {

  /**
   * The twitter post network plugin.
   *
   * @var \Drupal\social_post_example\Plugin\Network\TwitterPostInterface
   */
  protected $twitterPost;

  /**
   * The social post twitter entity storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
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
   * @var \Drupal\social_post_example\TwitterPostTokenManager
   */
  protected $tokenManager;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    /* @var \Drupal\social_post_example\Plugin\Network\TwitterPost $twitter_post*/
    $twitter_post = $container->get('plugin.network.manager')->createInstance('social_post_example');

    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $twitter_post,
      $container->get('entity_type.manager'),
      $container->get('current_user')
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
   * @param \Drupal\social_post_example\Plugin\Network\TwitterPostInterface $twitter_post
   *   The twitter post network plugin.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_manager
   *   The entity type manager.
   * @param \Drupal\Core\Session\AccountInterface $current_user
   *   The current user.
   */
  public function __construct(array $configuration,
                              $plugin_id,
                              $plugin_definition,
                              TwitterPostInterface $twitter_post,
                              EntityTypeManagerInterface $entity_manager,
                              AccountInterface $current_user) {

    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->twitterPost = $twitter_post;
    $this->twitterEntity = $entity_manager->getStorage('social_post_example_user');
    $this->currentUser = $current_user;
  }

  /**
   * Executes the action with the given context.
   *
   * @param string $status
   *   The tweet text.
   */
  protected function doExecute($status) {
    $accounts = $this->getTwitterAccountsByUserId($this->currentUser->id());

    /* @var \Drupal\social_post_example\Entity\TwitterUserInterface $account */
    foreach ($accounts as $account) {
      // Update status, If there was an error, boolean FALSE is returned.
      if (!$this->twitterPost->doPost($account->getAccessToken(), $account->getAccessTokenSecret(), $status)) {
        drupal_set_message('There was an error while updating Twitter status for ' . $account->getScreenName() . ', please review the logs.', 'error');
      }
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
