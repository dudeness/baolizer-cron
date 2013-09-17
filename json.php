<?php

/*
 * This file chaches the podio data to json
*/

require_once 'config.inc.php';
require_once PATH_INC.'/podio.config.inc.php';
require_once PATH_INC.'/podio.json.inc.php';
require_once PATH_LIB_PODIO.'/PodioAPI.php';

// set up podio
Podio::setup(PODIO_USER_ID, PODIO_USER_SECRET);
Podio::authenticate('app', array('app_id' => PODIO_APP_ID, 'app_token' => PODIO_APP_TOKEN));

// get podio's app field definitions
if ($podioConfig['filter_cache'] >= 1 && file_exists(PATH_TMP.'/podioAppInfo.cache')) $podioAppInfoResponse = unserialize(file_get_contents(PATH_TMP.'/podioAppInfo.cache'));
else {
  $podioAppInfoRequest = Podio::request('GET', '/app/'.PODIO_APP_ID);
  if (!$podioAppInfoRequest || $podioAppInfoRequest->status != 200) {
    die('workflow aborted api get:/app/'.PODIO_APP_ID);
  }
  $podioAppInfoResponse = json_decode($podioAppInfoRequest->body);

  if ($podioConfig['filter_cache'] >= 1) file_put_contents(PATH_TMP.'/podioAppInfo.cache', serialize($podioAppInfoResponse));
}
$podioFieldData = array();
foreach ($podioAppInfoResponse->fields as $fieldData) {
  $podioFieldData[$fieldData->external_id] = $fieldData;
}

// count rows in podio app
if ($podioConfig['filter_cache'] === 2 && file_exists(PATH_TMP.'/podioAppItemCount.cache')) $podioAppCountResponse = unserialize(file_get_contents(PATH_TMP.'/podioAppItemCount.cache'));
else {
  $podioAppCountRequest = Podio::request('GET', '/item/app/'.PODIO_APP_ID.'/count');
  if (!$podioAppCountRequest || $podioAppCountRequest->status != 200) {
    die('workflow aborted api get:/item/app/'.PODIO_APP_ID.'/count');
  }
  $podioAppCountResponse = json_decode($podioAppCountRequest->body);
  
  if ($podioConfig['filter_cache'] === 2) file_put_contents(PATH_TMP.'/podioAppItemCount.cache', serialize($podioAppCountResponse));
}

/*
 * $podioViewData is used to dynamically store the information given by
 * podio api to generate the requested data defined in podio.json.inc.php
 */
$podioViewData = array();
foreach (array_keys($jsonView) as $viewKey) {
  $podioViewData[$viewKey] = array();
}

/*
 * $jsonViewData is the final data structrue of $podioViewData
 */
$jsonViewData = array();
foreach (array_keys($jsonView) as $viewKey) {
  $podioViewData[$viewKey] = array();
}

// split podio filter requests to support more than 500 items in app
$podioFilterRequestRuns = ceil($podioAppCountResponse->count/$podioConfig['filter_split']);
for ($i=0;$i<$podioFilterRequestRuns;$i++) {
  if ($podioConfig['filter_cache'] === 2 && file_exists(PATH_TMP.'/podioAppItemsRun'.$i.'.cache')) $podioItemResponse = unserialize(file_get_contents(PATH_TMP.'/podioAppItemsRun'.$i.'.cache'));
  else {
    $podioItemRequest = Podio::request('POST', '/item/app/'.PODIO_APP_ID.'/filter', array(
      'limit' => $podioConfig['filter_split'],
      'offset' => ($podioConfig['filter_split'] * $i),
      'remember' => false,
    ));
    if (!$podioItemRequest || $podioItemRequest->status != 200) die ('workflow abortet in filter action');
    $podioItemResponse = json_decode($podioItemRequest->body);
    
    if ($podioConfig['filter_cache'] === 2) file_put_contents(PATH_TMP.'/podioAppItemsRun'.$i.'.cache', serialize($podioItemResponse));
  }
  foreach ($podioItemResponse->items as $itemRaw) {
    $processedConditionFields = array();
    $itemFieldData = array();
    foreach ($itemRaw->fields as $itemRawField) {
      // handle the latlng stuff configured in podio.config.inc.php
      if ($podioConfig['latlng']['enabled']) {
        //needs work
      }
      // handle the field stuff configured in podio.json.php
      foreach ($jsonView as $viewKey => $viewConfig) {
        if (!isset($podioViewData[$viewKey][$itemRaw->item_id])) {
          $podioViewData[$viewKey][$itemRaw->item_id] = array(
            'conditions' => array(),
            'fields' => array(),
            'sandbox' => array(),
          );
        }
        // check field for condition
        if (isset($viewConfig['conditions'][$itemRawField->external_id])) {
          
          switch ($itemRawField->type) {
            case 'text':
              if ($itemRawField->values[0]->value == $viewConfig['conditions'][$itemRawField->external_id])
                $podioViewData[$viewKey][$itemRaw->item_id]['conditions'][$itemRawField->external_id] = true;
              break;
            case 'state':
              if ($itemRawField->config->delta == $viewConfig['conditions'][$itemRawField->external_id])
                $podioViewData[$viewKey][$itemRaw->item_id]['conditions'][$itemRawField->external_id] = true;
              break;
            case 'category':
              if (isset($itemRawField->values[0]->value->id) && $itemRawField->values[0]->value->id == $viewConfig['conditions'][$itemRawField->external_id]) {
                $podioViewData[$viewKey][$itemRaw->item_id]['conditions'][$itemRawField->external_id] = true;
              }
              #$podioViewData[$viewKey][$itemRaw->item_id]['sandbox'][$itemRawField->external_id] = $itemRawField;
              break;
            default:
              $podioViewData[$viewKey][$itemRaw->item_id]['conditions']['_type_' . $itemRawField->external_id] = $itemRawField->type;
              break;
          }
        }
        //check if field is visible
        if (in_array($itemRawField->external_id, $viewConfig['fields'])) {
          $podioViewData[$viewKey][$itemRaw->item_id]['fields'][$itemRawField->external_id] = array('values' => array());
          foreach ($itemRawField->values as $num => $itemRawFieldValue) {
            switch ($itemRawField->type) {
            	case 'text':
            	case 'state':
            	  $podioViewData[$viewKey][$itemRaw->item_id]['fields'][$itemRawField->external_id]['values'][$num] = array(
            	  	'raw' => $itemRawFieldValue->value,
            	  	'value' => $itemRawFieldValue->value,
            	  );
            	  break;
            }
          }
        }
      }
    }
  }
}

/*
 * 
 */
foreach ($jsonView as $viewKey => $viewConfig) {
  //loop all $podioViewData to clean api data
  foreach ($podioViewData[$viewKey] as $itemId => $itemData) {
    
    // if number of loaded and validated condition of an item is equal to
    // num of conditions in podio.config.php, item is viewable
    if (count($viewConfig['conditions']) != count($itemData['conditions'])) {
      unset($podioViewData[$viewKey][$itemId]);
      continue;
    }
    
    // podio api gives not meta data of empty fields, so we add 'em
    foreach ($viewConfig['fields'] as $fieldExternalId) {
      if (!isset($podioViewData[$viewKey][$itemId]['fields'][$fieldExternalId])) {
        $podioViewData[$viewKey][$itemId]['fields'][$fieldExternalId] = array('values' => array());
      }
      // embed field meta data
      $podioViewData[$viewKey][$itemId]['fields'][$fieldExternalId]['external_id'] = $fieldExternalId;
      $podioViewData[$viewKey][$itemId]['fields'][$fieldExternalId]['type'] = $podioFieldData[$fieldExternalId]->type;
      $podioViewData[$viewKey][$itemId]['fields'][$fieldExternalId]['label'] = $podioFieldData[$fieldExternalId]->config->label;
      $podioViewData[$viewKey][$itemId]['fields'][$fieldExternalId]['weight'] = $podioFieldData[$fieldExternalId]->config->delta;
    }
    
    // link field result to $jsonViewData
    $jsonViewData[$viewKey][$itemId] =& $podioViewData[$viewKey][$itemId]['fields'];
  }
  
  //all $jsonViewData for current viewKey are ready to be stored in defined file
  if (!is_dir(PATH_HTDOCS.'/'.$viewKey)) mkdir(PATH_HTDOCS.'/'.$viewKey);
  file_put_contents(PATH_HTDOCS.'/'.$viewKey.'/'.PODIO_JSON_FILE_NAME, json_encode($jsonViewData[$viewKey]));
}