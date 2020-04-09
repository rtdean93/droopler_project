<?php

namespace Drupal\d_baselinker_ifirma\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\user\Entity\User;

/**
 * Baselinker configuration form.
 *
 * @package Drupal\d_baselinker\Form
 */
class IfirmaConfigurationForm extends FormBase {

  /**
   * {@inheritDoc}
   */
  public function getFormId() {
    return 'd_baselinker_ifirma_config_form';
  }

  /**
   * {@inheritDoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = \Drupal::service('config.factory')
      ->get('d_baselinker_ifirma.settings');
    $form['api_url'] = [
      '#type' => 'textfield',
      '#default_value' => $config->get('api_url'),
      '#title' => t('api_url'),
    ];
    $form['username'] = [
      '#type' => 'textfield',
      '#default_value' =>  $config->get('username'),
      '#title' => t('IFirma username'),
    ];
    $form['api_key'] = [
      '#type' => 'textfield',
      '#default_value' => $config->get('api_key'),
      '#title' => t('API key'),
    ];
    $form['key_type'] = [
      '#type' => 'textfield',
      '#default_value' => $config->get('key_type'),
      '#title' => t('Key type'),
    ];

    $form['actions'] = [
      '#type' => 'actions',
    ];
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => t('Submit'),
    ];

    return $form;
  }

  /**
   * {@inheritDoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = \Drupal::service('config.factory')
      ->getEditable('d_baselinker_ifirma.settings');
    $values = $form_state->getValues();
    $config->set('api_url', $values['api_url']);
    $config->set('username', $values['username']);
    $config->set('key_type', $values['key_type']);
    $config->set('api_key', $values['api_key']);
    $config->save();
  }

}
