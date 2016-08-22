<?php

namespace Drupal\social_post_twitter\Entity\Controller;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Routing\UrlGeneratorInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Defines a class to build a listing of Twitter user entities.
 *
 * @ingroup social_post_twitter
 */
class TwitterUserListBuilder extends EntityListBuilder {

  /**
   * The url generator.
   *
   * @var \Drupal\Core\Routing\UrlGeneratorInterface
   */
  protected $urlGenerator;

  /**
   * The user entity storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $userEntity;

  /**
   * {@inheritdoc}
   */
  public static function createInstance(ContainerInterface $container, EntityTypeInterface $entity_type) {
    return new static(
      $entity_type,
      $container->get('entity_type.manager')->getStorage($entity_type->id()),
      $container->get('entity_type.manager')->getStorage('user'),
      $container->get('url_generator')
    );
  }

  /**
   * TwitterUserListBuilder constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $entity_type
   *   The entity type.
   * @param \Drupal\Core\Entity\EntityStorageInterface $storage
   *   The entity storage for the social_post_twitter_user entity.
   * @param \Drupal\Core\Entity\EntityStorageInterface $user_entity
   *   The entity storage for the user entity.
   * @param \Drupal\Core\Routing\UrlGeneratorInterface $url_generator
   *   The url generator.
   */
  public function __construct(EntityTypeInterface $entity_type,
                              EntityStorageInterface $storage,
                              EntityStorageInterface $user_entity,
                              UrlGeneratorInterface $url_generator) {

    parent::__construct($entity_type, $storage);
    $this->urlGenerator = $url_generator;
    $this->userEntity = $user_entity;
  }

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['twitter_id'] = $this->t('Twitter ID');
    $header['screen_name'] = $this->t('Screen name');
    $header['user'] = $this->t('User ID');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /* @var $entity \Drupal\social_post_twitter\Entity\TwitterUser */
    $row['twitter_id'] = $entity->getTwitterId();
    $row['screen_name'] = $entity->getScreenName();

    $user = $this->userEntity->load($entity->getUserId());
    $row['user'] = $user->toLink();

    return $row + parent::buildRow($entity);
  }

}
