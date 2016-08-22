<?php

namespace Drupal\social_post_twitter;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\Query\QueryFactory;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Utility\Token;

/**
 * Process tokens for Twitter status.
 */
class TwitterPostTokenManager {
  /**
   * The token utility.
   *
   * @var \Drupal\Core\Utility\Token
   */
  protected $token;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityManager;

  /**
   * The route match.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * The entity query.
   *
   * @var \Drupal\Core\Entity\Query\QueryFactoryInterface
   */
  protected $entityQuery;

  /**
   * TwitterPostTokenManager constructor.
   *
   * @param \Drupal\Core\Utility\Token $token
   *   The token utility.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_manager
   *   The entity type manager.
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The route match.
   * @param \Drupal\Core\Session\AccountInterface $current_user
   *   The current user.
   * @param \Drupal\Core\Entity\Query\QueryFactory $entity_query
   *   The entity query.
   */
  public function __construct(Token $token,
                              EntityTypeManagerInterface $entity_manager,
                              RouteMatchInterface $route_match,
                              AccountInterface $current_user,
                              QueryFactory $entity_query) {

    $this->token = $token;
    $this->entityManager = $entity_manager;
    $this->routeMatch = $route_match;
    $this->currentUser = $current_user;
    $this->entityQuery = $entity_query;
  }

  /**
   * Formats the status replacing the tokens.
   *
   * @param string $status
   *   The raw status value.
   *
   * @return string
   *   The processed status value.
   */
  public function formatStatus($status) {
    $data = $this->getDataArray($status);

    return $this->token->replace($status, $data);
  }

  /**
   * Returns the data array for token replacing.
   *
   * @param string $text
   *   The string to process.
   *
   * @return array
   *   The data array with entities that replace the tokens.
   *
   * @TODO This will be deleted when tokens are supported by rules.
   *
   * @see https://www.drupal.org/node/2632564.
   */
  protected function getDataArray($text) {
    $data = [];

    $tokens = $this->token->scan($text);

    foreach (array_keys($tokens) as $token) {
      switch ($token) {
        case 'node':
          $node = $this->routeMatch->getParameter('node');
          if ($node) {
            $data['node'] = $node;
          } else { // This approach is used when a new node is created.
            $nid = $this->entityQuery->getAggregate('node', 'AND')
                            ->aggregate('nid', 'MAX')
                            ->execute()[0]['nid_max'];

            $data['node'] = $this->entityManager->getStorage('node')->load($nid);
          }
          break;

        case 'user':
          $data['user'] = $this->entityManager->getStorage('user')->load($this->currentUser->id());
          break;

      }
    }

    return $data;
  }

}
