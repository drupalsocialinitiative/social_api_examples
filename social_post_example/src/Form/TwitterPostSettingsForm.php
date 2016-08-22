<?php

namespace Drupal\social_post_twitter\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Settings form for Social Post Twitter.
 */
class TwitterPostSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['social_post_twitter.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'social_post_twitter.form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('social_post_twitter.settings');

    $form['twitter_settings'] = array(
      '#type' => 'details',
      '#title' => $this->t('Twitter settings'),
      '#open' => TRUE,
    );

    $form['twitter_settings']['consumer_key'] = array(
      '#type' => 'textfield',
      '#required' => TRUE,
      '#title' => $this->t('Consumer Key (API Key)'),
      '#default_value' => $config->get('consumer_key'),
      '#description' => $this->t('Copy the Consumer Key here'),
    );

    $form['twitter_settings']['consumer_secret'] = array(
      '#type' => 'textfield',
      '#required' => TRUE,
      '#title' => $this->t('Consumer Secret (API Secret)'),
      '#default_value' => $config->get('consumer_secret'),
      '#description' => $this->t('Copy the Consumer Secret here'),
    );

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();

    $this->config('social_post_twitter.settings')
      ->set('consumer_key', $values['consumer_key'])
      ->set('consumer_secret', $values['consumer_secret'])
      ->save();

    parent::submitForm($form, $form_state);
  }

}
