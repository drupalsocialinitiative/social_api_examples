<?php

namespace Drupal\social_auth_example;

use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\social_auth\AuthManager\OAuth2Manager;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Contains all the logic for Google OAuth2 authentication.
 */
class ExampleAuthManager extends OAuth2Manager {

  /**
   * Constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactory $configFactory
   *   Used for accessing configuration object factory.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   The logger factory.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   Used to get the authorization code from the callback request.
   */
  public function __construct(ConfigFactory $configFactory,
                              LoggerChannelFactoryInterface $logger_factory,
                              RequestStack $request_stack) {

    parent::__construct($configFactory->get('social_auth_example.settings'),
                        $logger_factory,
                        $this->request = $request_stack->getCurrentRequest());
  }

  /**
   * {@inheritdoc}
   *
   * Once the user authorizes the app, the provider returns an authorization
   * code. This method exchanges this code for a token.
   */
  public function authenticate() {
    try {
      $this->setAccessToken($this->client->getAccessToken('authorization_code',
        ['code' => $this->request->query->get('code')]));
    }
    catch (IdentityProviderException $e) {
      $this->loggerFactory->get('social_auth_example')
        ->error('There was an error during authentication. Exception: ' . $e->getMessage());
    }
  }

  /**
   * {@inheritdoc}
   *
   * Requests the endpoint that would return the basic user information. The
   * library we're using (The League) abstracts us from this implementation by
   * providing the method getResourceOwner.
   */
  public function getUserInfo() {
    if (!$this->user) {
      $this->user = $this->client->getResourceOwner($this->getAccessToken());
    }

    return $this->user;
  }

  /**
   * {@inheritdoc}
   *
   * Generates the authentication URL the user will be redirected to for
   * authentication. This URL often includes the scopes we want to request from
   * the provider.
   */
  public function getAuthorizationUrl() {
    $scopes = [
      'email',
      'profile',
    ];

    $extra_scopes = $this->getScopes();
    if ($extra_scopes) {
      $scopes = array_merge($scopes, explode(',', $extra_scopes));
    }

    // Returns the URL where user will be redirected.
    return $this->client->getAuthorizationUrl([
      'scope' => $scopes,
    ]);
  }

  /**
   * {@inheritdoc}
   *
   * This method allows the implementer to provide an interface for requesting
   * a resource from the provider. It is helpful for external modules that
   * want to request more data and is also used when extra endpoints are
   * configured in the settings form.
   *
   * @see \Drupal\social_auth\AuthManager\OAuth2Manager::getExtraDetails
   */
  public function requestEndPoint($method, $path, $domain = NULL, array $options = []) {
    if (!$domain) {
      $domain = 'https://www.googleapis.com';
    }

    $url = $domain . $path;

    $request = $this->client->getAuthenticatedRequest($method, $url, $this->getAccessToken(), $options);

    try {
      return $this->client->getParsedResponse($request);
    }
    catch (IdentityProviderException $e) {
      $this->loggerFactory->get('social_auth_example')
        ->error('There was an error when requesting ' . $url . '. Exception: ' . $e->getMessage());
    }

    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getState() {
    return $this->client->getState();
  }

}
