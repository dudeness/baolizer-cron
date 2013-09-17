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
      while (preg_match('/\@([a-zA-Z0-9]*)\:([a-zA-Z0-9\-\_\/\.\:\?]*)\;/i', $tplString, $matches)) {
        $tplSearch = $matches[0];
        $tplReplace = '';
        $args = explode(':', $matches[2]);
        switch ($matches[1]) {
          case 'res':
            $resType = array_shift($args);
            switch ($resType) {
              case 'img':
                if (file_exists(PATH_RES.'/img/'.$args[0])) {
                  $fileSplit = explode('.', $args[0]);
                  $tplReplace = 'data:image/'.end($fileSplit).';base64,'.base64_encode(file_get_contents(PATH_RES.'/img/'.$args[0]));
                } else $tplReplace = '';
                break;
              default:
                if (file_exists(PATH_RES.'/' . $resType . '/'.$args[0])) {
                  $tplReplace = file_get_contents(PATH_RES.'/' . $resType . '/'.$args[0]);
                } else $tplReplace = '';
                break;
            }
            break;
          case 'json':
            $valFinder = $itemData;
            $valBreadcrumbs = array();
            // follow @json: declaration in template
            while (($arg = array_shift($args)) !== NULL) {
              //support operations on current branch signed by ?
              if (strpos($arg, '?') === 0) {
                switch ($arg) {
                  case '?count':
                    $tplReplace = count($valFinder);
                    break;
                  case '?gettype':
                    $tplReplace = count($valFinder);
                    break;
                }
                break;
              }
              // dive in json
              if (isset($valFinder[$arg])) {
                $valFinder = $valFinder[$arg];
                $valBreadcrumbs[] = $arg;
              }
              else {
                $tplReplace = 'Unable to locate '.implode('/', $valBreadcrumbs).'/<em>'.$arg.'</em>';
                break;
              }
              // handle last branch
              if (!count($args)) {
                $tplReplace = $valFinder;
              }
            }
            break;
          case 'render':
            $renderField = array_shift($args);
            
            break;
        }
        $tplString = str_replace($tplSearch, $tplReplace, $tplString);
      }

      ob_start();
      eval('?>'.$tplString);
      $tplString = ob_get_contents();
      ob_end_clean();
      file_put_contents(PATH_HTDOCS.'/'.$viewKey.'/'.PODIO_JSON_DIR_WEBVIEW.'/'.$itemId.'.'.$viewMode.'.html', $tplString);
      print '<a href="http://' . $viewKey . '.baolizer.oc/webview/'.$itemId.'.'.$viewMode.'.html" target="_blank">'.$itemId.'.'.$viewMode.'</a><br />'."\n";
    }
  }
}