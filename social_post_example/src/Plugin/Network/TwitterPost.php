<?php

namespace Drupal\social_post_example\Plugin\Network;

use Abraham\TwitterOAuth\TwitterOAuth;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Logger\LoggerChannelTrait;
use Drupal\Core\Render\MetadataBubblingUrlGenerator;
use Drupal\social_api\SocialApiException;
use Drupal\social_post\Plugin\Network\SocialPostNetwork;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Defines Social Post Twitter Network Plugin.
 *
 * This is the main definition of the Network Plugin. The most important
 * properties are listed below.
 *
 * id: The unique identifier of this Network Plugin. It must have the same name
 * as the module itself.
 *
 * social_network: The Social Network for which this Network Plugin is defined.
 *
 * type: The type of the Network Plugin:
 * - social_auth: A Network Plugin for user login/registration.
 * - social_post: A Network Plugin for autoposting tasks.
 * - social_widgets: A Network Plugin for social networks' widgets.
 *
 * handlers: Defined the settings manager and the configuration identifier
 * in the configuration manager. In detail:
 *
 * - settings: The settings management for this Network Plugin.
 *   - class: The class for getting the configuration data. The settings
 *     property of this class is the instance of the class declared in this
 *     field.
 *   - config_id: The configuration id. It usually is the same used by the
 *     configuration form.
 *
 * @see Drupal\social_post_example\Form\TwitterPostSettingsForm
 *
 * @Network(
 *   id = "social_post_example",
 *   social_network = "Twitter",
 *   type = "social_post",
 *   handlers = {
 *     "settings": {
 *        "class": "\Drupal\social_post_example\Settings\TwitterPostSettings",
 *        "config_id": "social_post_example.settings"
 *      }
 *   }
 * )
 */
class TwitterPost extends SocialPostNetwork implements TwitterPostInterface {

  use LoggerChannelTrait;

  /**
   * The url generator.
   *
   * @var \Drupal\Core\Render\MetadataBubblingUrlGenerator
   */
  protected $urlGenerator;

  /**
   * Twitter connection.
   *
   * @var TwitterOAuth
   */
  protected $connection;

  /**
   * The tweet text.
   *
   * @var string
   */
  protected $status;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $container->get('url_generator'),
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager'),
      $container->get('config.factory')
    );
  }

  /**
   * TwitterPost constructor.
   *
   * @param \Drupal\Core\Render\MetadataBubblingUrlGenerator $url_generator
   *   Used to generate a absolute url for authentication.
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The configuration factory.
   */
  public function __construct(MetadataBubblingUrlGenerator $url_generator,
                              array $configuration,
                              $plugin_id,
                              $plugin_definition,
                              EntityTypeManagerInterface $entity_type_manager,
                              ConfigFactoryInterface $config_factory) {

    parent::__construct($configuration, $plugin_id, $plugin_definition, $entity_type_manager, $config_factory);

    $this->urlGenerator = $url_generator;
  }

  /**
   * {@inheritdoc}
   *
   * Initializes the Google SDK to request Google Accounts.
   *
   * The returning value of this method is what is returned when an instance of
   * this Network Plugin called the getSdk method.
   *
   * @see Drupal\social_post_example\Controller\TwitterPostController::redirectToTwitter
   */
  protected function initSdk() {
    $class_name = '\Abraham\TwitterOAuth\TwitterOAuth';
    if (!class_exists($class_name)) {
      throw new SocialApiException(sprintf('The PHP SDK for Twitter could not be found. Class: %s.', $class_name));
    }

    /* @var \Drupal\social_post_example\Settings\TwitterPostSettings $settings */
    $settings = $this->settings;

    return new TwitterOAuth($settings->getConsumerKey(), $settings->getConsumerSecret());
  }

  /**
   * {@inheritdoc}
   *
   * Perform the posting tasks to the social network.
   */
  public function post() {
    if (!$this->connection) {
      throw new SocialApiException('Call post() method from its wrapper doPost()');
    }

    $post = $this->connection->post('statuses/update', ['status' => $this->status]);

    if (isset($post->error)) {
      $this->getLogger('social_post_example')->error($post->error);
      return FALSE;
    }

    return TRUE;
  }

  /**
   * {@inheritdoc}
   *
   * Wrapper to post() method.
   *
   * This method sets the property that will be used by post(), so it can
   * be defined with the requirement of your specific socila network.
   *
   * For Twitter, we need to pass an access token and an access secret to
   * connect to the API, and we need a status message that will be tweeted.
   */
  public function doPost($access_token, $access_token_secret, $status) {
    $this->connection = $this->getSdk2($access_token, $access_token_secret);
    $this->status = $status;
    return $this->post();
  }

  /**
   * {@inheritdoc}
   */
  public function getOauthCallback() {
    return $this->urlGenerator->generateFromRoute('social_post_example.callback', [], ['absolute' => TRUE]);
  }

  /**
   * {@inheritdoc}
   */
  public function getSdk2($oauth_token, $oauth_token_secret) {
    /* @var \Drupal\social_post_example\Settings\TwitterPostSettings $settings */
    $settings = $this->settings;

    return new TwitterOAuth($settings->getConsumerKey(), $settings->getConsumerSecret(),
                $oauth_token, $oauth_token_secret);
  }

}
