<?php


namespace Drupal\social_auth_subscriber\EventSubscriber;

use Drupal\Core\Messenger\MessengerInterface;
use Drupal\social_api\Plugin\NetworkManager;
use Drupal\social_auth\AuthManager\OAuth2ManagerInterface;
use Drupal\social_auth\Event\SocialAuthEvents;
use Drupal\social_auth\Event\SocialAuthUserEvent;
use Drupal\social_auth\SocialAuthDataHandler;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Example of how to request more data.
 *
 * This example shows how to use the OAuth2 Manager provided by Social Auth
 * implementers to request more data.
 *
 * @see \Drupal\social_auth_example\ExampleAuthManager
 *
 * @package Drupal\social_auth_subscriber\EventSubscriber
 */
class RequestMoreData implements EventSubscriberInterface {

  /**
   * The messenger service.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  private $messenger;

  /**
   * The data handler.
   *
   * @var \Drupal\social_auth\SocialAuthDataHandler
   */
  private $dataHandler;

  /**
   * The network plugin manager.
   *
   * @var \Drupal\social_auth\SocialAuthDataHandler
   */
  private $networkManager;

  /**
   * The provider auth manager.
   *
   * @var \Drupal\social_auth\AuthManager\OAuth2ManagerInterface
   */
  private $providerAuth;

  /**
   * SocialAuthSubscriber constructor.
   *
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger service.
   * @param \Drupal\social_auth\SocialAuthDataHandler $data_handler
   *   Used to manage session variables.
   * @param \Drupal\social_api\Plugin\NetworkManager $network_manager
   *   Used to get an instance of the social auth implementer network plugin.
   * @param \Drupal\social_auth\AuthManager\OAuth2ManagerInterface $providerAuth
   *   Used to get the provider auth manager.
   */
  public function __construct(MessengerInterface $messenger,
                              SocialAuthDataHandler $data_handler,
                              NetworkManager $network_manager,
                              OAuth2ManagerInterface $providerAuth) {

    $this->messenger = $messenger;
    $this->dataHandler = $data_handler;
    $this->networkManager = $network_manager;
    $this->providerAuth = $providerAuth;
  }

  /**
   * {@inheritdoc}
   *
   * Returns an array of event names this subscriber wants to listen to.
   * For this case, we are going to subscribe for user login event and call the
   * methods to react on these events.
   */
  public static function getSubscribedEvents() {
    $events[SocialAuthEvents::USER_LOGIN] = ['onUserLogin'];

    return $events;
  }

  /**
   * Sets a drupal message when a user logs in.
   *
   * @param \Drupal\social_auth\Event\SocialAuthUserEvent $event
   *   The Social Auth user event object.
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   */
  public function onUserLogin(SocialAuthUserEvent $event) {
    $this->messenger->addStatus('User has logged in with a Social Auth implementer. Implementer for ' . $event->getPluginId());

    // Sets prefix.
    $this->dataHandler->setSessionPrefix($event->getPluginId());

    // Gets client object.
    $client = $this->networkManager->createInstance($event->getPluginId())->getSdk();

    // Create provider OAuth2 manager.
    // Can also use $client directly and request data using the library/SDK.
    $this->providerAuth->setClient($client)
      ->setAccessToken($this->dataHandler->get('access_token'));

    // Gets user info.
    $userInfo = $this->providerAuth->getUserInfo();

    // Sets fields.
    $fields['first_name'] = $userInfo->getFirstName();
    $fields['last_name'] = $userInfo->getLastName();

    $this->messenger->addStatus('First name: ' . $fields['first_name']);
    $this->messenger->addStatus('Last name: ' . $fields['last_name']);

    // Example: Requesting an endpoint and use the retrieved data.
    // Make sure you have requested the scope that allows you access to this
    // endpoint. The configuration should be in the settings form of the
    // Social Auth implementer.
    $data = $this->providerAuth->requestEndPoint('GET', '/youtube/v3/playlists?maxResults=2&mine=true&part=snippet');

    $this->messenger->addStatus('Number of playlists: ' . $data['pageInfo']['totalResults']);
  }

}
