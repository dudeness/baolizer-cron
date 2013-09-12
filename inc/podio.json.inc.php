<?php 

define('PODIO_JSON_FILE_NAME', 'items.json');
define('PODIO_JSON_DIR_WEBVIEW', 'webview');

$jsonView = array();

$viewName = 'public';
$jsonView[$viewName] = array();
$jsonView[$viewName]['fields'] = array(
  'company-or-organisation',
  'telefon',
  'street-address',
  'zip-codepost-code',
  'city',
  'state-provins-or-territory',
);
$jsonView[$viewName]['conditions'] = array(
  'typ' => '1', // so called 'einzelhandel',
  #'google-handlerkarte' => '1', // spot on map
);

$jsonView[$viewName]['webview'] = array(
  'enabled' => true,
  'modes' => array(
    'page', 'item.iphone', 'item.android'  
  ),
);


$viewName = 'staff';
$jsonView[$viewName] = array();
$jsonView[$viewName]['fields'] = array(
  'company-or-organisation',
  'telefon',
  'street-address',
  'zip-codepost-code',
  'city',
  'state-provins-or-territory',
);
$jsonView[$viewName]['conditions'] = array(
  #'typ' => '1', // so called 'einzelhandel',
  #'google-handlerkarte' => '1', // spot on map
);

$jsonView[$viewName]['webview'] = true;

