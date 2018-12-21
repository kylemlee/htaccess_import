<?php

use Drupal\redirect\Entity\Redirect;

function redirect_importer_finished($success, $results, $operations) {
  drupal_set_message(t('Redirect import has been completed.'));
  return t('import has been completed.');
}

/**
 * Process a single item
 */
function redirect_importer($item, &$context) {
    $logger = \Drupal::logger('redirect_importer');
    try { 
        Redirect::create([
          'redirect_source' => $item[1],
          'redirect_redirect' =>  (strpos($item[2], '/') === 0) ? 'internal:'.$item[2] : $item[2],
          'language' => 'und',
          'status_code' => '301',
        ])->save();

    }  catch (\Exception $e) {
      $logger->error( '' . $e->getMessage());
    }
}