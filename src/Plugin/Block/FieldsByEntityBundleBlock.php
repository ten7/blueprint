<?php

namespace Drupal\blueprint\Plugin\Block;

use Drupal\content_model_documentation\Entity\CMDocumentInterface;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\field\Entity\FieldConfig;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a blueprint: fields by node bundle block.
 *
 * @Block(
 *   id = "blueprint_fields_by_entity_bundle",
 *   admin_label = @Translation("Blueprint: Fields by Entity Bundle"),
 *   category = @Translation("Blueprint")
 * )
 */
class FieldsByEntityBundleBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The current route match.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * The entity field manager.
   *
   * @var \Drupal\Core\Entity\EntityFieldManagerInterface
   */
  protected $entityFieldManager;

  /**
   * Constructs a new FieldsByNodeBundleBlock instance.
   *
   * @param array $configuration
   *   The plugin configuration, i.e. an array with configuration values keyed
   *   by configuration option name. The special key 'context' may be used to
   *   initialize the defined contexts by setting it to an array of context
   *   values keyed by context names.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The current route match.
   * @param \Drupal\Core\Entity\EntityFieldManagerInterface $entity_field_manager
   *   The entity field manager.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, RouteMatchInterface $route_match, EntityFieldManagerInterface $entity_field_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->routeMatch = $route_match;
    $this->entityFieldManager = $entity_field_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('current_route_match'),
      $container->get('entity_field.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {

    $build = [];

    // Check to see if we are on a content model document.
    $cm_document = $this->routeMatch->getParameter('cm_document');
    $route_name = $this->routeMatch->getRouteName();
    if (!$cm_document instanceof CMDocumentInterface) {
      return $build;
    }

    $rows = [];

    $fields = $this->entityFieldManager->getFieldDefinitions($cm_document->getDocumentedEntityParameter('type'),
      $cm_document->getDocumentedEntityParameter('bundle'));

    foreach ($fields as $field) {
      if (!$field instanceof FieldConfig) {
        continue;
      }
      $rows[] = [
        $field->getLabel(),
        $field->getName(),
        $field->getType(),
        $field->getDescription(),
      ];

      $build['content'] = [
        '#type' => 'table',
        '#header' => [t('Label'), t('Name'), t('Type'), t('Description')],
        '#rows' => $rows,
        '#cache' => [
          'contexts' => [
            'url',
          ],
        ],
      ];
    }

    return $build;
  }



}
