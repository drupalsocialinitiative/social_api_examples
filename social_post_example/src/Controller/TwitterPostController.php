<?php

namespace Drupal\social_post_twitter\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\social_api\Plugin\NetworkManager;
use Drupal\social_post_twitter\TwitterPostAuthManager;
use Drupal\social_post_twitter\TwitterUserEntityManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Zend\Diactoros\Response\RedirectResponse;

/**
 * Manages requests to Twitter.
 */
class TwitterPostController extends ControllerBase {

  /**
   * The network plugin manager.
   *
   * @var \Drupal\social_api\Plugin\NetworkManager
   */
  protected $networkManager;

  /**
   * The twitter post auth manager.
   *
   * @var \Drupal\social_post_twitter\TwitterPostAuthManager
   */
  protected $authManager;

  /**
   * The Twitter user entity manager.
   *
   * @var \Drupal\social_post_twitter\TwitterUserEntityManager
   */
  protected $twitterEntity;

  /**
   * TwitterPostController constructor.
   *
   * @param \Drupal\social_api\Plugin\NetworkManager $network_manager
   *   The network plugin manager.
   * @param \Drupal\social_post_twitter\TwitterPostAuthManager $auth_manager
   *   The Twitter post auth manager.
   * @param \Drupal\social_post_twitter\TwitterUserEntityManager $twitter_entity
   *   The Twitter user entity manager.
   */
  public function __construct(NetworkManager $network_manager, TwitterPostAuthManager $auth_manager, TwitterUserEntityManager $twitter_entity) {
    $this->networkManager = $network_manager;
    $this->authManager = $auth_manager;
    $this->twitterEntity = $twitter_entity;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('plugin.network.manager'),
      $container->get('twitter_post.auth_manager'),
      $container->get('twitter_user_entity.manager')
    );
  }

  /**
   * Redirects user to Twitter for authentication.
   *
   * @return \Zend\Diactoros\Response\RedirectResponse
   *   Redirects to Twitter.
   *
   * @throws \Abraham\TwitterOAuth\TwitterOAuthException
   */
  public function redirectToTwitter() {
    /* @var TwitterPost $network_plugin */
    $network_plugin = $this->networkManager->createInstance('social_post_twitter');

    /* @var TwitterOAuth $connection */
    $connection = $network_plugin->getSdk();

    $request_token = $connection->oauth('oauth/request_token', array('oauth_callback' => $network_plugin->getOauthCallback()));

    // Saves the request token values in session.
    $this->authManager->setOauthToken($request_token['oauth_token']);
    $this->authManager->setOauthTokenSecret($request_token['oauth_token_secret']);

    // Generates url for authentication.
    $url = $connection->url('oauth/authorize', array('oauth_token' => $request_token['oauth_token']));

    return new RedirectResponse($url);
  }

  /**
   * Callback function for the authentication process.
   *
   * @throws \Abraham\TwitterOAuth\TwitterOAuthException
   */
  public function callback() {
    $oauth_token = $this->authManager->getOauthToken();
    $oauth_token_secret = $this->authManager->getOauthTokenSecret();

    /* @var TwitterOAuth $connection */
    $connection = $this->networkManager->createInstance('social_post_twitter')->getSdk2($oauth_token, $oauth_token_secret);

    // Gets the permanent access token.
    $access_token = $connection->oauth('oauth/access_token', array('oauth_verifier' => $this->authManager->getOauthVerifier()));

    // Save the user authorization tokens and store the current user id in $uid.
    $uid = $this->twitterEntity->saveUser($access_token);

    return $this->redirect('entity.user.edit_form', array('user' => $uid));
  }

}
