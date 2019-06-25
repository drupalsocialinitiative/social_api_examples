<?php

namespace Drupal\social_auth_subscriber\EventSubscriber;

use Drupal\Core\Messenger\MessengerInterface;
use Drupal\social_auth\Event\SocialAuthEvents;
use Drupal\social_auth\Event\UserEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Drupal\social_auth\Event\UserFieldsEvent;

/**
 * Example of how to subscribe to events when user is created or logged in.
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
    $events[SocialAuthEvents::USER_FIELDS] = ['onUserFields'];

    return $events;
  }

  /**
   * Alters the user name if the user is being created by Social Auth.
   *
   * @param \Drupal\social_auth\Event\UserEvent $event
   *   The Social Auth user event object.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function onUserCreated(UserEvent $event) {

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
   * @param \Drupal\social_auth\Event\UserEvent $event
   *   The Social Auth user event object.
   */
  public function onUserLogin(UserEvent $event) {
    $this->messenger->addStatus('User has logged in with a Social Auth implementer. Implementer for ' . $event->getPluginId());
  }

  /**
   * Prints a drupal message with the current and updated user name.
   *
   * You can initialized custom user fields or change initialized values as
   * needed.
   *
   * @param \Drupal\social_auth\Event\UserFieldsEvent $event
   *   The Social Auth user event object.
   */
  public function onUserFields(UserFieldsEvent $event) {
    $fields = $event->getUserFields();

    $this->messenger->addStatus('Field mail has been initialized to ' . $fields['mail']);

    $fields['mail'] = 'updated_' . $fields['mail'];

    $this->messenger->addStatus('Field mail has been updated to ' . $fields['mail']);

    // Assumming we want to store the user picture somewhere else other than
    // 'user_picture'.
    $fields['picture_id'] = $fields['user_picture'];
    unset($fields['user_picture']);

    // Assumming foo is an user field.
    $fields['foo'] = 'bar';

    $event->setUserFields($fields);
  }

}
