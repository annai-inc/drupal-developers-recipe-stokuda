<?php

namespace Drupal\hello_world\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\hello_world\EchoMessageServiceInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Hello World block.
 *
 * @Block(
 *  id = "hello_world_block",
 *  admin_label = @Translation("Hello world block"),
 * )
 */
class HelloWorldBlock extends BlockBase implements ContainerFactoryPluginInterface {

  use StringTranslationTrait;

  /**
   * The messenger service.
   *
   * @var \Drupal\hello_world\EchoMessageServiceInterface
   */
  protected $messenger;
  /**
   * Construct.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param string $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\hello_world\EchoMessageServiceInterface $messenger
   *   The messenger service.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EchoMessageServiceInterface $messenger) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->messenger = $messenger;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('hello_world.messenger')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $content = $this->messenger->helloWorld();
    if ($this->configuration['emphasize']) {
      $content = '<em>' . $content . '</em>';
    }

    return [
      '#markup' => $content,
    ];
  }

  /**
   * {@inheritDoc}
   */
  public function defaultConfiguration() {
    return [
      'emphasize' => 0,
    ];
  }

  /**
   * {@inheritDoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    /** @var array $config */
    $config = $this->getConfiguration();

    $form['emphasize'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Emphasize message'),
      '#description' => $this->t('Check this box if you want to emphasize message'),
      '#default_value' => $config['emphasize'],
    ];

    return $form;
  }

  /**
   * {@inheritDoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $this->configuration['emphasize'] = $form_state->getValue('emphasize');
  }
}
