<?php

namespace Drupal\social_auth_example;

use Drupal\social_auth\AuthManager\OAuth2Manager;
use Symfony\Component\HttpFoundation\RequestStack;
use Google_Service_Oauth2;

/**
 * Manages the authentication requests.
 *
 * This class is specific to Google authentication, although it is extended
 * from OAuth2Manager class.
 *
 * @see \Drupal\social_auth\AuthManager\OAuth2Manager
 */
class GoogleAuthManager extends OAuth2Manager {

  /**
   * The request object.
   *
   * @var \Symfony\Component\HttpFoundation\Request
   */
  protected $request;

  /**
   * The Google service client.
   *
   * @var \Google_Client
   */
  protected $client;

  /**
   * Code returned by Google for authentication.
   *
   * @var string
   */
  protected $code;

  /**
   * GoogleLoginManager constructor.
   *
   * @param \Symfony\Component\HttpFoundation\RequestStack $request
   *   Used to get the parameter code returned by Google.
   */
  public function __construct(RequestStack $request) {
    $this->request = $request->getCurrentRequest();
  }

  /**
   * {@inheritdoc}
   */
  public function authenticate() {
    $this->client->setAccessToken($this->getAccessToken());
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getAccessToken() {
    if (!$this->accessToken) {
      $this->accessToken = $this->client->fetchAccessTokenWithAuthCode($this->getCode());
    }

    return $this->accessToken;
  }

  /**
   * {@inheritdoc}
   */
  public function getUserInfo() {
    return $this->getOauth2Service()->userinfo->get();
  }

  /**
   * Gets Google Oauth2 Service.
   *
   * @return Google_Service_Oauth2
   *   The Google Oauth2 service.
   */
  protected function getOauth2Service() {
    return new Google_Service_Oauth2($this->getClient());
  }

  /**
   * Gets the code returned by Google to authenticate.
   *
   * @return string
   *   The code string returned by Google.
   */
  protected function getCode() {
    if (!$this->code) {
      $this->code = $this->request->query->get('code');
    }

    return $this->code;
  }

}
