<?php

require_once 'config.inc.php';
require_once PATH_INC.'/podio.json.inc.php';

foreach ($jsonView as $viewKey => $viewConfig) {
  // get index of all files listed in webview directory
  #$webviewDir = readdir(PATH_HTDOCS.'/'.$viewKey.'/'.PODIO_JSON_DIR_WEBVIEW);
  #$webviewIndex = array();
  #while(!feof($webviewDir)) {
  #  
  #}
  if (!isset($viewConfig['webview']['enabled']) || !$viewConfig['webview']['enabled']) continue;
  if (!is_dir(PATH_HTDOCS.'/'.$viewKey.'/'.PODIO_JSON_DIR_WEBVIEW)) mkdir(PATH_HTDOCS.'/'.$viewKey.'/'.PODIO_JSON_DIR_WEBVIEW);
  $viewData = json_decode(file_get_contents(PATH_HTDOCS.'/'.$viewKey.'/'.PODIO_JSON_FILE_NAME), TRUE);
  file_exists(PATH_TPL.'/item.'.$viewKey.'.php') ? $tplFile = PATH_TPL.'/item.'.$viewKey.'.php' : $tplFile = PATH_TPL.'/item.default.php';
  $tplSource = file_get_contents($tplFile);
  
  if (!isset($viewConfig['webview']['modes']) || !is_array($viewConfig['webview']['modes']) || !count($viewConfig['webview']['modes'])) $viewConfig['webview']['modes'] = array('default');
  
  foreach ($viewConfig['webview']['modes'] as $viewMode) {
    foreach ($viewData as $itemId => $itemData) {
      $tplString = $tplSource;
      // handle @*:* declarations
      $matches = array();
      while (preg_match('/\@([a-zA-Z0-9]*)\:([a-zA-Z0-9\-\_\/\.\:]*)\;/i', $tplString, $matches)) {
        $tplSearch = $matches[0];
        $tplReplace = '';
        $args = explode(':', $matches[2]);
        switch ($matches[1]) {
          case 'res':
            switch ($args[0]) {
              case 'img':
                if (file_exists(PATH_RES.'/img/'.$args[1])) {
                  $fileSplit = explode('.', $args[1]);
                  $tplReplace = 'data:image/'.end($fileSplit).';base64,'.base64_encode(file_get_contents(PATH_RES.'/img/'.$args[1]));
                } else $tplReplace = '';
                break;
              default:
                if (file_exists(PATH_RES.'/'.$args[0].'/'.$args[1])) {
                  $tplReplace = file_get_contents(PATH_RES.'/'.$args[0].'/'.$args[1]);
                } else $tplReplace = '';
                break;
            }
            break;
          case 'podio':
            $itemValFinder = $itemData;
            $itemValBreadcrumbs = array();
            $itemValDepth = 0;
            while (($arg = array_shift($args)) !== NULL) {
              if (is_numeric($arg)) $arg = intval($arg);
              array_push($itemValBreadcrumbs, $arg);
              
              #$tplReplace .= '('.gettype($arg).')'.$arg.' | ';
              #continue;
              $itemValDepth++;
              if (isset($itemValFinder[$arg])) {
                $itemValFinder &= $itemValFinder[$arg];
                
                #ob_start();
                ##print_r($itemValFinder['company-or-organisation']['0']);
                #print_r($itemValFinder);
                #$tplReplace = ob_get_contents();
                #ob_end_clean();
                #
                #
                #break;
                
                
                $tplReplace = 'VALUE ' . $arg;
                if (count($args) == 0) {
                  $tplReplace = 'reached';
                  switch (gettype($itemValFinder)) {
                  	case 'string':
                  	case 'integer':
                  	  $tplReplace = '#'.implode('/', $itemValBreadcrumbs).':'.'('.gettype($itemValFinder).')'.$itemValFinder;
                  	  break;
                  	default:
                  	  $tplReplace = '#'.implode('/', $itemValBreadcrumbs).':'.'unprintable('.gettype($itemValFinder).')';
                  }
                }
                else {
                  #if ()
                }
              }
              else {
                $tplReplace = '#'.implode('/', $itemValBreadcrumbs).':unlocatable('.$arg.')';
              }
            }
          default:
            #$tplReplace[0] = '';
            break;
        }
        $tplString = str_replace($tplSearch, $tplReplace, $tplString);
        #break;
      }
      /*
       if (preg_match_all('/\@([a-zA-Z0-9]*)\:([a-zA-Z0-9\-\_\/\.]*)\:([a-zA-Z0-9\-\_\/\.]*)\:?([a-zA-Z0-9\-\_\/\.\:]*)\;/i', $tplString, $matches)) {
      $tplSearch = array();
      $tplReplace = array();
      foreach ($matches[0] as $i => $origString) {
      $tplSearch[] = $origString;
      switch ($matches[1][$i]) {
      case 'res':
      switch ($matches[2][$i]) {
      case 'img':
      if (file_exists(PATH_RES.'/img/'.$matches[3][$i])) {
      $fileSplit = explode('.', $matches[3][$i]);
      $tplReplace[] = 'data:image/'.end($fileSplit).';base64,'.base64_encode(file_get_contents(PATH_RES.'/img/'.$matches[3][$i]));
      } else $tplReplace[] = '';
      break;
      default:
      $tplReplace[] = '';
      break;
      }
      break;
      case 'podio':
      $tplReplace[] = '';
      break;
      default:
      $tplReplace[] = '';
      }
      }
      $tplString = str_replace($tplSearch, $tplReplace, $tplString);
    
      }
      */
      /*
       // replace field data
      foreach ($itemData as $fieldExternalId => $fieldData) {
      $tplSearch[] = '/\[podio\:'.$fieldExternalId.'\:value\]/';
      $tplReplace[] = $fieldData['value'];
      $tplSearch[] = '/\[podio\:'.$fieldExternalId.'\:raw\]/';
      $tplReplace[] = $fieldData['raw'];
      }
      $tplString = preg_replace($tplSearch, $tplReplace, $tplSource);
    
      // check for conditions
      $conditionMatches = array();
      if (preg_match_all('/\[if\:value\:(.*)\](.*)\[\/if\]/i', $tplString, $conditionMatches)) {
      $tplSearchCondition = array();
      $tplRepalceCondition = array();
      foreach ($conditionMatches[0] as $count => $matchString) {
      $tplSearchCondition[] = $matchString;
      if (strlen($conditionMatches[1][$count])) $tplRepalceCondition[] = $conditionMatches[2][$count];
      else $tplRepalceCondition[] = '';
      }
      $tplString = str_replace($tplSearchCondition, $tplRepalceCondition, $tplString);
      }
    
      */
      ob_start();
      eval('?>'.$tplString);
      $tplString = ob_get_contents();
      ob_end_clean();
      file_put_contents(PATH_HTDOCS.'/'.$viewKey.'/'.PODIO_JSON_DIR_WEBVIEW.'/'.$itemId.'.'.$viewMode.'.html', $tplString);
      print '<a href="http://' . $viewKey . '.baolizer.oc/webview/'.$itemId.'.'.$viewMode.'.html" target="_blank">'.$itemId.'.'.$viewMode.'</a><br />'."\n";
    }
    
  }
  
}