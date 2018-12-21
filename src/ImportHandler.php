<?php

namespace Drupal\htaccess_import;

/**
 * ImportHanlder.
 */
class ImportHandler{ 

    protected $logger;

    public function __construct() {
        $this->logger = \Drupal::logger('ImportHanlder');
    }

    public function readFile() {
      try {
        $items=[];
        $handle = fopen(DRUPAL_ROOT . "/.htaccess", "r");
        if ($handle) {
            while (($line = fgets($handle)) !== false) {
              $filter_redirect_rules = preg_split('/\s+/', ltrim($line," "));
              if ( reset($filter_redirect_rules) == 'RewriteRule' && in_array('[L,R=301]',$filter_redirect_rules) ){
                if (stripos(json_encode($filter_redirect_rules),'REQUEST_URI') == false) {
                    $items[] = array_filter($filter_redirect_rules);
                 }
              }
            }
          fclose($handle);
        } 
      } catch (Exception $e) {
        \Drupal::logger('ImportHandler')->error($e->getMessage());
      }
      return $items;
    }

}