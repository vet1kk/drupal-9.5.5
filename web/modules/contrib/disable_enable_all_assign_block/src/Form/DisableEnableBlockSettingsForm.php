<?php

namespace Drupal\disable_enable_all_assign_block\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Database\Database;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Component\Datetime\TimeInterface;
use Drupal\block\BlockRepositoryInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityStorageInterface;

/**
 * Disable and enable all assign block advanced settings.
 */
class DisableEnableBlockSettingsForm extends ConfigFormBase {

  /**
   * The user storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $storage;

  /**
   * The cache render service.
   *
   * @var \Drupal\Core\Cache\CacheBackendInterface
   */
  protected $cacheRender;

  /**
   * The time service.
   *
   * @var \Drupal\Component\Datetime\TimeInterface
   */
  protected $time;

  /**
   * The block repository.
   *
   * @var \Drupal\block\BlockRepositoryInterface
   */
  protected $blockRepository;

  /**
   * Constructs a \Drupal\toolbar_themes\ToolbarThemesSettingsForm object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   * @param \Drupal\Core\Entity\EntityStorageInterface $storage
   *   The user storage.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cacheRender
   *   A cache backend interface instance.
   * @param \Drupal\Component\Datetime\TimeInterface $time
   *   The time service.
   * @param \Drupal\block\BlockRepositoryInterface $block_repository
   *   The block repository.
   */
  public function __construct(ConfigFactoryInterface $config_factory, EntityStorageInterface $storage, CacheBackendInterface $cacheRender, TimeInterface $time, BlockRepositoryInterface $block_repository) {
    parent::__construct($config_factory);
    $this->storage = $storage;
    $this->cacheRender = $cacheRender;
    $this->time = $time;
    $this->blockRepository = $block_repository;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
       $container->get('entity_type.manager')->getStorage('block'),
      $container->get('cache.config'),
      $container->get('datetime.time'),
      $container->get('block.repository')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'disable_enable_all_assign_block_settings_advanced';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'disable_enable_all_assign_block.settings_advanced',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);
    $settings = $this->config('disable_enable_all_assign_block.settings_advanced');
    // Load all region content assigned via blocks.
    $cacheable_metadata_list = [];
    $region = $this->blockRepository->getVisibleBlocksPerRegion($cacheable_metadata_list);
    $region_list = json_decode(json_encode(system_region_list($this->config('system.theme')->get('default'), $region)), TRUE);
    $form['#tree'] = TRUE;
    $form['daab_region'] = [
      '#type'          => 'checkboxes',
      '#title' => $this->t('All region'),
      '#options' => $region_list,
      '#default_value' => $settings->get('daab_region') ?:[],
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Get frontend theme name.
    $defaultThemeName = $this->config('system.theme')->get('default');
    // Get db conntection.
    $connection = Database::getConnection();
    // Admin setting config.
    $settings = $this->configFactory->getEditable('disable_enable_all_assign_block.settings_advanced');
    // Set value.
    $settings->set('daab_region', $form_state->getValue('daab_region'))->save();
    // Fetch all block config from the table.
    $regions = [];
    $extensions = $connection->select('config')
      ->fields('config', ['data'])
      ->condition('collection', '')
      ->condition('name', 'block.block.%', 'LIKE')
      ->execute()->fetchCol();
    foreach ($extensions as $svalue) {
      $unserialize = unserialize($svalue);
      if ($unserialize['theme'] == $defaultThemeName) {
        $regions[$unserialize['id']] = $unserialize['region'];
      }
    }
    // Get checked checkbox resgion and it's block ID
    // Disable all block for checkbox checked resgion
    $match_region = array_keys(array_intersect($regions,$form_state->getValue('daab_region')));
    foreach ($match_region as $svalue) {
      $block = $this->storage->load($svalue);
      $op = 'disable';
      $block->$op()->save();
    }

    // Get unchecked checkbox resgion and it's block ID
    // Enable all block for checkbox unchecked resgion
    $match_region = array_keys(array_diff($regions,$form_state->getValue('daab_region')));
    foreach ($match_region as $svalue) {
      $block = $this->storage->load($svalue);
      $op = 'enable';
      $block->$op()->save();
    }
    $this->messenger()->addMessage($this->t('Configuration has been set.'));
  }

}
