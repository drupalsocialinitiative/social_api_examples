<?php

namespace Drupal\social_auth_subscriber\EventSubscriber;

use Drupal\Core\Messenger\MessengerInterface;
use Drupal\social_auth\Event\SocialAuthEvents;
use Drupal\social_auth\Event\SocialAuthUserEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Example of how to subscribe to events when user is created or logged in.
 *
 * This example shows how to use the OAuth2 Manager provided by Social Auth
 * implementers to request more data.
 *
 * @package Drupal\social_auth_subscriber\EventSubscriber
 */
class UserCreationAndLogin implements EventSubscriberInterface {

  /**
   * The messenger service.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  private $messenger;

  /**
   * SocialAuthSubscriber constructor.
   *
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger service.
   */
  public function __construct(MessengerInterface $messenger) {
    $this->messenger = $messenger;
  }

  /**
   * {@inheritdoc}
   *
   * Returns an array of event names this subscriber wants to listen to.
   * For this case, we are going to subscribe for user creation and login
   * events and call the methods to react on these events.
   */
  public static function getSubscribedEvents() {
    $events[SocialAuthEvents::USER_CREATED] = ['onUserCreated'];
    $events[SocialAuthEvents::USER_LOGIN] = ['onUserLogin'];

    return $events;
  }

  /**
   * Alters the user name if the user is being created by Social Auth.
   *
   * @param \Drupal\social_auth\Event\SocialAuthUserEvent $event
   *   The Social Auth user event object.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function onUserCreated(SocialAuthUserEvent $event) {

    /*
     * @var Drupal\user\UserInterface $user
     *
     * For all available methods, see User class
     * @see https://api.drupal.org/api/drupal/core!modules!user!src!Entity!User.php/class/User
     */
    $user = $event->getUser();

    // Adds a prefix with the implementer id on username.
    $user->setUsername($event->getPluginId() . ' ' . $user->getDisplayName())->save();
  }

  /**
   * Sets a drupal message when a user logs in.
   *
   * @param \Drupal\social_auth\Event\SocialAuthUserEvent $event
   *   The Social Auth user event object.
   */
  public function onUserLogin(SocialAuthUserEvent $event) {
    $this->messenger->addStatus('User has logged in with a Social Auth implementer. Implementer for ' . $event->getPluginId());
  }

}
