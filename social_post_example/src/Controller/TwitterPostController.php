<?php

namespace Drupal\social_post_example\Controller;

use Abraham\TwitterOAuth\TwitterOAuth;
use Drupal\Core\Controller\ControllerBase;
use Drupal\social_api\Plugin\NetworkManager;
use Drupal\social_post_example\Plugin\Network\TwitterPost;
use Drupal\social_post_example\TwitterPostAuthManager;
use Drupal\social_post_example\TwitterUserEntityManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Zend\Diactoros\Response\RedirectResponse;

/**
 * Manages requests to Twitter.
 *
 * Most of the code here is specific to implement a Twitter authentication
 * process. Social Networking services might require different approaches. Use
 * this class as an example of how to implement your specific module.
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
   * @var \Drupal\social_post_example\TwitterPostAuthManager
   */
  protected $authManager;

  /**
   * The Twitter user entity manager.
   *
   * @var \Drupal\social_post_example\TwitterUserEntityManager
   */
  protected $twitterEntity;

  /**
   * TwitterPostController constructor.
   *
   * @param \Drupal\social_api\Plugin\NetworkManager $network_manager
   *   The network plugin manager.
   * @param \Drupal\social_post_example\TwitterPostAuthManager $auth_manager
   *   The Twitter post auth manager.
   * @param \Drupal\social_post_example\TwitterUserEntityManager $twitter_entity
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
   * Most of the Social Networks' API require you to redirect users to a
   * authentication page. This method is not a mandatory one, instead you must
   * adapt to the requirements of the module you are implementing.
   *
   * This method is called in 'social_post_example.redirect_to_twitter' route.
   * @see social_post_example.routing.yml.
   *
   * This method is triggered when the user loads user/social-post/example/auth.
   * It creates an instance of the Network Plugin 'social_post_example' and
   * returns an instance of the \Abraham\TwitterOAuth\TwitterOAuth object.
   *
   * After the user grants permission, Twitter redirects him to a callback url
   * specified in the request. In this case, it should redirects to
   * 'user/social-post/example/auth/callback', which calls the callback method.
   *
   * @return \Zend\Diactoros\Response\RedirectResponse
   *   Redirects to Twitter.
   *
   * @throws \Abraham\TwitterOAuth\TwitterOAuthException
   */
  public function redirectToTwitter() {
    /* @var TwitterPost $network_plugin */
    // Creates an instance of the social_post_example Network Plugin.
    $network_plugin = $this->networkManager->createInstance('social_post_example');

    /* @var TwitterOAuth $connection */
    /* Gets the Twitter SDK.
     *
     * Notice that getSdk() does not require any argument, whereas getSdk2()
     * does.
     *
     * Your social network might not require to have different ways of getting
     * an instance of the SDK, but Twitter does.
     */
    $connection = $network_plugin->getSdk();

    // Requests Twitter to get temporary tokens.
    $request_token = $connection->oauth('oauth/request_token', array('oauth_callback' => $network_plugin->getOauthCallback()));

    // Saves the temporary token values in session.
    $this->authManager->setOauthToken($request_token['oauth_token']);
    $this->authManager->setOauthTokenSecret($request_token['oauth_token_secret']);

    // Generates url for user authentication.
    $url = $connection->url('oauth/authorize', array('oauth_token' => $request_token['oauth_token']));

    // Redirects the user to allow him to grant permissions.
    return new RedirectResponse($url);
  }

  /**
   * Callback function for the authentication process.
   *
   * Most of the Social Networks' API redirects to a callback url. This method
   * is not a mandatory one, instead you must adapt to the requirements of the
   * module you are implementing.
   *
   * This method is called in 'social_post_example.callback' route.
   * @see social_post_example.routing.yml.
   *
   * This method is triggered when the path
   * user/social-post/example/auth/callback is loaded. It creates an instance of
   * the Network Plugin 'social_post_example' and authenticates the user.
   *
   * After the user is authenticated, it associates the Twitter account to the
   * user.
   *
   * @throws \Abraham\TwitterOAuth\TwitterOAuthException
   */
  public function callback() {
    // Gets the temporary token values stored by the redirectToTwitter() method.
    $oauth_token = $this->authManager->getOauthToken();
    $oauth_token_secret = $this->authManager->getOauthTokenSecret();

    /* @var TwitterOAuth $connection */
    /* Creates an instance of the Network Plugin and gets the SDK.
     *
     * Notice that this is the getSdk2() method. This is different from getSdk()
     * as it allows to create an instance of the SDK by passing values for
     * the auth token and auth token secret.
     *
     * Your social network might not require to have different ways of getting
     * an instance of the SDK, but Twitter does.
     */
    $connection = $this->networkManager->createInstance('social_post_example')->getSdk2($oauth_token, $oauth_token_secret);

    // Gets the permanent access token.
    $access_token = $connection->oauth('oauth/access_token', array('oauth_verifier' => $this->authManager->getOauthVerifier()));

    // Save the user permanent tokens to use them when necessary.
    $uid = $this->twitterEntity->saveUser($access_token);

    // Returns to the user edit form.
    return $this->redirect('entity.user.edit_form', array('user' => $uid));
  }

}
