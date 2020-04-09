<?php

namespace Drupal\d_baselinker\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\user\Entity\User;

/**
 * Baselinker configuration form.
 *
 * @package Drupal\d_baselinker\Form
 */
class BaselinkerConfigurationForm extends FormBase {

  /**
   * {@inheritDoc}
   */
  public function getFormId() {
    return 'd_baselinker_config_form';
  }

  /**
   * {@inheritDoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = \Drupal::service('config.factory')
      ->get('d_baselinker.settings');
    $baselinkerUserId = $config->get('baselinker_user');
    if (!empty($baselinkerUserId)) {
      $baselinkerUser = User::load($baselinkerUserId);
    }
    $form['communication_password'] = [
      '#type' => 'textfield',
      '#default_value' => $config->get('communication_password'),
      '#title' => t('Communication password'),
    ];
    $form['baselinker_user'] = [
      '#type' => 'entity_autocomplete',
      '#target_type' => 'user',
      '#default_value' => $baselinkerUser,
      '#title' => t('Baselinker user'),
      '#description' => t('User allowed to see baselinker data, used for communication with baselinker.'),
    ];
    $form['integration_file'] = [
      '#type' => 'fieldset',
      '#title' => t('Integration file information'),
      '#description' => t('Data from original baselinker integration file needed for correct module function.'),
    ];
    $form['integration_file']['version'] = [
      '#type' => 'textfield',
      '#default_value' => $config->get('version'),
      '#title' => t('Integration file version'),
    ];
    $form['integration_file']['standard'] = [
      '#type' => 'textfield',
      '#default_value' => $config->get('standard'),
      '#title' => t('Integration file standard'),
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
      ->getEditable('d_baselinker.settings');
    $values = $form_state->getValues();
    $config->set('communication_password', $values['communication_password']);
    $config->set('baselinker_user', $values['baselinker_user']);
    $config->set('version', $values['version']);
    $config->set('standard', $values['standard']);
    $config->save();
  }

}
