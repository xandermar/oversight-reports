<?php

namespace Drupal\oversight_reports\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class DefaultForm.
 */
class DefaultForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'oversight_reports.default',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'oversight_reports';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('oversight_reports.default');
    $form['agency_id'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Agency ID'),
      '#description' => $this->t('Enter the ID of the Agency used by Oversight.Gov'),
      '#maxlength' => 64,
      '#size' => 64,
      '#default_value' => $config->get('agency_id'),
      '#required' => true,
    ];
    // example of how to add feature to allow ONLY certain fields
    
    // $form['available_fields'] = [
    //   '#type' => 'checkboxes',
    //   '#title' => $this->t('Available Fields'),
    //   '#description' => $this->t('Fields available on Oversight.Gov. NOTE: Only fields with data will appear on reports.'),
    //   '#options' => [
    //     'submitting_oig' => $this->t('Submitting OIG'), 
    //     'report_description' => $this->t('Report Description')
    //   ],
    //   '#default_value' => $config->get('available_fields'),      
    //   '#attributes' => array('class' => array('available-fields')),     
    // ];
    $form['refresh_interval'] = [
      '#type' => 'select',
      '#options' => array(
        '15' => '15 minutes',
        '30' => '30 minutes',
        '45' => '45 minutes',
        '60' => '60 minutes',
      ),
      '#title' => $this->t('Refresh Interval'),
      '#description' => $this->t('The interval in which reports from Oversight.Gov are refreshed.'),
      '#default_value' => $config->get('refresh_interval'),
    ];
    $form['include_recommendations'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Include Recommendations'),
      '#description' => $this->t('If checked, each report will list associated recommendations as posted in Oversight.Gov'),
      '#default_value' => $config->get('include_recommendations'),
    ];
     
    $form['markup'] = [
      '#markup' => $this->t('<div>See the <a href="/reports">reports page</a>.</div>'),
    ];
    // $form['#attached']['library'][] = 'oversight_reports/oversight_reports';
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    $this->config('oversight_reports.default')
      ->set('agency_id', $form_state->getValue('agency_id'))
      ->set('refresh_interval', $form_state->getValue('refresh_interval'))
      ->set('available_fields', $form_state->getValue('available_fields'))
      ->set('include_recommendations', $form_state->getValue('include_recommendations'))
      ->save();
  }

}