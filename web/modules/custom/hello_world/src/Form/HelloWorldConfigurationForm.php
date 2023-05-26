<?php

namespace Drupal\hello_world\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\FormBase;
use Drupal\user\PermissionHandlerInterface;
use Drupal\user\RoleStorageInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Form\ConfigFormBaseTrait;
use Drupal\Core\Config\ConfigFactoryInterface;

/**
 * Configuration form for hellow_world module.
 */
class HelloWorldConfigurationForm extends FormBase {

  use ConfigFormBaseTrait;

  protected $permissionHandler;

  protected $roleStorage;

  public string $target_permission = 'can say something';

  public function __construct(PermissionHandlerInterface $permission_handler, RoleStorageInterface $role_storage, ConfigFactoryInterface $config_factory) {
    $this->permissionHandler = $permission_handler;
    $this->roleStorage = $role_storage;
    $this->setConfigFactory($config_factory);
  }

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('user.permissions'),
      $container->get('entity_type.manager')->getStorage('user_role'),
      $container->get('config.factory')
    );
  }

  private function getRoles() {
    return $this->roleStorage->loadMultiple();
  }

  /**
   * {@inheritDoc}
   */
  protected function getEditableConfigNames() {
    return ['hello_world.settings'];
  }

  /**
   * {@inheritDoc}
   */
  public function getFormId() {
    return 'hello_world_configuration_form';
  }

  public function validateForm(array &$form, FormStateInterface $form_state)
  {
    $forbidden_message =  $form_state->getValue('forbidden_message');
    $forbidden_message = str_replace(array("\r\n", "\r", "\n"), "\n", $forbidden_message);
    foreach(explode("\n", $forbidden_message) as $one_line_message){
      if (strlen($one_line_message) == 0) {
        $form_state->setErrorByName("禁止文字列", $this->t('"禁止文字列を入力"に空行は含められません。'));
      }
    }
    parent::validateForm($form, $form_state);
  }

  public function buildForm(array $form, FormStateInterface $form_state){
    $config = $this->config('hello_world.settings');
    $options = [];
    $default_values = [];
    foreach ($this->getRoles() as $role_name => $role) {
      if ($role_name == "administrator") {
        continue;
      }
      $options[$role_name] = $this->t($role->label());
      foreach ($role->getPermissions() as $idx => $permission) {
        if ($permission == $this->target_permission) {
          array_push($default_values, $role_name);
        }
      }
    }
    $form['role_names'] = [
      '#type' => 'checkboxes',
      '#options' => $options,
      '#title' => 'ロールを選択',
      '#description' => 'Please select roles to grant on /say_something',
      '#default_value' => $default_values,
    ];
    $form['forbidden_message'] = [
      '#type' => 'textarea',
      '#title' => '禁止文字列を入力',
      '#default_value' => $config->get('forbidden_message'),
    ];
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save permissions'),
      '#button_type' => 'primary',
    ];
    return $form;
  }

  /**
   * {@inheritDoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    foreach ($form_state->getValue('role_names') as $role_name => $name) {
      if ($name != 0) {
        user_role_change_permissions($role_name, Array(
          $this->target_permission => 1
        ));
      } else {
        user_role_change_permissions($role_name, Array(
          $this->target_permission => 0
        ));
      }
    }
    $this->config('hello_world.settings')
      ->set('forbidden_message', $form_state->getValue('forbidden_message'))
      ->save();
    $this->messenger()->addStatus($this->t('The changes have been saved.'));
  }

}
