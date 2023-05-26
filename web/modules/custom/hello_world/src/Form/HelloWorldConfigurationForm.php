<?php

namespace Drupal\hello_world\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configuration form for hellow_world module.
 */
class HelloWorldConfigurationForm extends ConfigFormBase {

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

  /**
   * {@inheritDoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('hello_world.settings');

    $form['hello_message'] = [
      '#type' => 'textfield',
      '#title' => 'Hello message',
      '#description' => 'Please provide a funny hello message for your site vistors.',
      '#default_value' => $config->get('hello_message'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritDoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('hello_world.settings')
      ->set('hello_message', $form_state->getValue('hello_message'))
      ->save();

    parent::submitForm($form, $form_state);
  }

}
