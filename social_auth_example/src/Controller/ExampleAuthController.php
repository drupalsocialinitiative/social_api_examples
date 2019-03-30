<?php

namespace Drupal\social_auth_example\Controller;

use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\social_api\Plugin\NetworkManager;
use Drupal\social_auth\Controller\OAuth2ControllerBase;
use Drupal\social_auth\SocialAuthDataHandler;
use Drupal\social_auth\User\UserAuthenticator;
use Drupal\social_auth_example\ExampleAuthManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Manages requests to Google API.
 *
 * Most of the code here is specific to implement a Google login process, but it
 * is widely applied to providers using the OAuth2 protocol. However, some
 * services might require different approaches. Use this class as an example of
 * how to implement your specific module.
 */
class ExampleAuthController extends OAuth2ControllerBase {

  /**
   * ExampleAuthController constructor.
   *
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger service.
   * @param \Drupal\social_api\Plugin\NetworkManager $network_manager
   *   Used to get an instance of social_auth_example network plugin.
   * @param \Drupal\social_auth\User\UserAuthenticator $user_authenticator
   *   Used to manage user authentication/registration.
   * @param \Drupal\social_auth_example\ExampleAuthManager $example_manager
   *   Used to manage authentication methods.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request
   *   Used to access GET parameters.
   * @param \Drupal\social_auth\SocialAuthDataHandler $data_handler
   *   The Social Auth data handler (used for session management).
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   Used to handle metadata for redirection to authentication URL.
   */
  public function __construct(MessengerInterface $messenger,
                              NetworkManager $network_manager,
                              UserAuthenticator $user_authenticator,
                              ExampleAuthManager $example_manager,
                              RequestStack $request,
                              SocialAuthDataHandler $data_handler,
                              RendererInterface $renderer) {

    parent::__construct(
      'Social Auth Example', 'social_auth_example',
      $messenger, $network_manager, $user_authenticator,
      $example_manager, $request, $data_handler, $renderer);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('messenger'),
      $container->get('plugin.network.manager'),
      $container->get('social_auth.user_authenticator'),
      $container->get('social_auth_example.manager'),
      $container->get('request_stack'),
      $container->get('social_auth.data_handler'),
      $container->get('renderer')
    );
  }

  /**
   * Callback function to login user.
   *
   * Most of the providers' API redirects to a callback url. Most work for the
   * callback of an OAuth2 protocol is implemented in processCallback. However,
   * you should adapt this method to the provider's requirements.
   *
   * This method is called in 'social_auth_example.callback' route.
   *
   * @see social_auth_example.routing.yml
   * @see \Drupal\social_auth\Controller\OAuth2ControllerBase::processCallback
   *
   * This method is triggered when the path user/login/example/callback is
   * requested. It calls processCallback which creates an instance of
   * the Network Plugin 'social auth example'. It later authenticates the user
   * and creates the service to obtain data about the user.
   */
  public function callback() {
    // Checks if authentication failed.
    if ($this->request->getCurrentRequest()->query->has('error')) {
      $this->messenger->addError($this->t('You could not be authenticated.'));

      return $this->redirect('user.login');
    }

    /* @var \League\OAuth2\Client\Provider\GoogleUser|null $profile */
    $profile = $this->processCallback();

    // If authentication was successful.
    if ($profile !== NULL) {

      // Gets (or not) extra initial data.
      $data = $this->userAuthenticator->checkProviderIsAssociated($profile->getId()) ? NULL : $this->providerManager->getExtraDetails();

      return $this->userAuthenticator->authenticateUser($profile->getName(), $profile->getEmail(), $profile->getId(), $this->providerManager->getAccessToken(), $profile->getAvatar(), $data);
    }

    return $this->redirect('user.login');
  }

}
