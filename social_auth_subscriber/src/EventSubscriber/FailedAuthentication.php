<?php

namespace Drupal\social_auth_subscriber\EventSubscriber;

use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Routing\UrlGeneratorTrait;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Url;
use Drupal\social_auth\Event\BeforeRedirectEvent;
use Drupal\social_auth\Event\FailedAuthenticationEvent;
use Drupal\social_auth\Event\SocialAuthEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Reacts on Social Auth before redirect and failed authentication events.
 */
class FailedAuthentication implements EventSubscriberInterface {

  use StringTranslationTrait;
  use UrlGeneratorTrait;

  /**
   * Used to access GET parameters.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $request;

  /**
   * The messenger service.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  private $messenger;

  /**
   * SocialAuthSubscriber constructor.
   *
   * @param \Symfony\Component\HttpFoundation\RequestStack $request
   *   Request stack.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger service.
   */
  public function __construct(RequestStack $request,
                              MessengerInterface $messenger) {
    $this->request = $request;
    $this->messenger = $messenger;
  }

  /**
   * {@inheritdoc}
   *
   * Returns an array of event names this subscriber wants to listen to.
   * For this case, we are going to subscribe for before provider redirect and
   * provider error and call the methods to react on these events.
   */
  public static function getSubscribedEvents() {
    $events[SocialAuthEvents::BEFORE_REDIRECT] = ['beforeProviderRedirect'];
    $events[SocialAuthEvents::FAILED_AUTH] = ['onProviderError'];

    return $events;
  }

  /**
   * Sets a redirect back session value.
   *
   * This is used in case there was a failed authentication. The user, in this
   * case, will be redirected to the page from where he started the
   * authentication process.
   *
   * @param \Drupal\social_auth\Event\BeforeRedirectEvent $event
   *   The provider redirect event object.
   */
  public function beforeProviderRedirect(BeforeRedirectEvent $event) {
    global $base_url;

    // Stores the current path to redirect the user in case of and error.
    $error_destination = str_replace($base_url, '', $this->request->getCurrentRequest()->server->get('HTTP_REFERER'));
    $event->getDataHandler()->set('redirect_back', $error_destination);
  }

  /**
   * When authentication in provider fails.
   *
   * @param \Drupal\social_auth\Event\FailedAuthenticationEvent $event
   *   The Social Auth provider error event object.
   */
  public function onProviderError(FailedAuthenticationEvent $event) {
    // Logs the error message to debug.
    $this->messenger->addError(
      $this->t('User failed to log in with a Social Auth implementer. For @provider and faced error @error', [
        '@provider' => $event->getPluginId(),
        '@error' => $event->getError(),
      ])
    );

    // Uses the redirect_back value from the data handler to redirect user.
    if ($error_dest = $event->getDataHandler()->get('redirect_back')) {
      $event->getDataHandler()->set('redirect_back', NULL);
      $redirectPath = Url::fromUserInput($error_dest);
      $event->setResponse($this->redirect($redirectPath->getRouteName(), $redirectPath->getRouteParameters()));
    }
  }

}
