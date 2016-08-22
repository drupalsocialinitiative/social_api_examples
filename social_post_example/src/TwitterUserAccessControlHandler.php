<?php

namespace Drupal\social_post_example;

use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Access controller for the Twitter user entity.
 *
 * @see \Drupal\social_post_example\Entity\TwitterUser.
 *
 * This is more related to the Entity API.
 * @see https://goo.gl/3DICwY
 */
class TwitterUserAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    switch ($operation) {
      case 'view':
        return AccessResult::allowedIfHasPermissions($account,
          array('view social post user entity lists',
            'delete own social post user accounts',
          ), 'OR');

      case 'delete':
        return AccessResult::allowedIfHasPermission($account, 'delete social post user entity lists');
    }

    // Unknown operation, no opinion.
    return AccessResult::neutral();
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermission($account, 'add social post user entities');
  }

}
