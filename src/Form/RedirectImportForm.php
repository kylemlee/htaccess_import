<?php

namespace Drupal\htaccess_import\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\htaccess_import\ImportHandler;

class RedirectImportForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'redirect_import_form';
  }
  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['htaccess_import.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('htaccess_import.settings');
    $handler = new ImportHandler();
    $redirects = $handler->readFile();
    $rows = [];

    $form['submit'] = [
      '#type'  => 'submit',
      '#weight' => -1,
      '#value' => t('Start Import'),
    ];

    $form['import_preview_table'] = [
      '#type' => 'table',
      '#caption' => t('Import Preview'),
      '#header' => [
        t('Source URL'),
        t('Redirect URL'),
        t('In database'),
      ],
    ];

    foreach ($redirects as $key => $item) {
      $rows[$key]['source_url'] = $item[1];
      $rows[$key]['redirect_url'] = $item[2];
      $rid = \Drupal::entityQuery('redirect')
        ->condition('redirect_source.path', $item[1])
        ->execute();
      $rows[$key]['in_database'] = (empty($rid)) ? 'No' : 'Yes';
    }
    $form['import_preview_table']['#rows'] = $rows;
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    $batch = [
      'title'            => t('Import redirect rules from htaccess'),
      'operations'       => [],
      'init_message'     => t('Commencing'),
      'progress_message' => t('Processed @current out of @total.'),
      'error_message'    => t('An error occurred during processing'),
      'finished'         => 'redirect_importer_finished',
      'file'             => drupal_get_path('module', 'htaccess_import') . '/htaccess_import.batch.inc',
    ];

    $handler = new ImportHandler();
    $redirects = $handler->readFile();


    foreach($redirects as $index => $item) { 

      $rid = \Drupal::entityQuery('redirect')
      ->condition('redirect_source.path', $item[1])
      ->execute();

      if ( empty($rid) ) {
        $batch['operations'][] = [
          'redirect_importer', [$item],
        ];
      }

    }
    
    if ( !empty($batch['operations']) ) {
      batch_set($batch);
    }else {
      drupal_set_message(t('There is nothing to import..'));
    }

  }

}
