<?php

namespace Drupal\social_auth_consumer\Controller;

use Drupal\social_api\Plugin\NetworkManager;
use Drupal\social_auth_example\ExampleAuthManager;
use Drupal\social_post\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Handles requests to get more data from Google using Social Auth Example.
 */
class SocialAuthConsumerController extends ControllerBase {

  /**
   * The provider manager.
   *
   * @var \Drupal\social_auth_example\ExampleAuthManager
   */
  protected $providerManager;

  protected $networkManager;

  /**
   * SocialAuthConsumerController constructor.
   *
   * @param \Drupal\social_auth_example\ExampleAuthManager $provider_manager
   *   The provider manager to make requests to Google.
   * @param \Drupal\social_api\Plugin\NetworkManager $network_manager
   *   Used to get an instance of social_auth_example network plugin.
   */
  public function __construct(ExampleAuthManager $provider_manager, NetworkManager $network_manager) {
    $this->providerManager = $provider_manager;
    $this->networkManager = $network_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('social_auth_example.manager'),
      $container->get('plugin.network.manager')
    );
  }

  /**
   * Gets data from Google using the provider manager.
   */
  public function consume() {

    /** @var \League\OAuth2\Client\Provider\Google|false $client */
    $client = $this->networkManager->createInstance('social_auth_example')->getSdk();

    // Sets the library client.
    $this->providerManager->setClient($client);

    $token = $this->getToken();

    // Sets the access token before making any requests.
    $this->providerManager->setAccessToken($token);

    // Example: Requesting an endpoint and use the retrieved data.
    // Make sure you have requested the scope that allows you access to this
    // endpoint. The configuration should be in the settings form of the
    // Social Auth implementer.
    $data = $this->providerManager->requestEndPoint('GET', '/youtube/v3/playlists?maxResults=2&mine=true&part=snippet');
    $playlists = $data['pageInfo']['totalResults'];

    return [
      '#markup' => $this->t('Number of playlists: @playlists', ['@playlists' => $playlists]),
    ];
  }

  /**
   * Returns the token for the provider.
   *
   * @return string
   *   The access token for the given user.
   */
  private function getToken() {
    /** @var \Drupal\social_auth\Entity\SocialAuth[] $socialAuthUser */
    $socialAuthUser = $this->entityTypeManager()->getStorage('social_auth')
      ->loadByProperties([
        'plugin_id' => 'social_auth_example',
        'user_id' => $this->currentUser()->id(),
      ]);

    return current($socialAuthUser)->get('token')->getValue()[0]['value'];
  }

}
