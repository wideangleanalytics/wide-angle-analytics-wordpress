<?php
include_once( $this->plugin->folder . '/types/WideAngleExclusion.php' );

class WideAngleHelpers {



  function normalizeBoolean($value) {
    $trimmed = trim($value);
    switch($trimmed) {
      case "":
      case "false":
      case "off":
        return "false";
      case "on":
      case "true":
        return "true";
    }
  }

  function normalizeTrackerDomain($domain) {
    return "https://" . parse_url($domain,  PHP_URL_HOST);
  }

  function parseIncludeParamsSetting($params) {
    if(trim($params) !== "") {
      return  preg_split("/\|\:/", $params);
    } else {
      return array();
    }
  }

  function parseRequestIncludeParams($request) {
    $pattern = "/^waa_inc_params_(\d{1,2})$/";
    $params = array();
    foreach($request as $key => $value) {
      if(preg_match($pattern, $key)) {
        array_push($params, trim($value));
      }
    }
    return $params;
  }

  function parseExclusionSetting($path) {
    $flatExclusions     = $path;
    $exclusions = preg_split("/\|\:/", $flatExclusions);
    $exclusionPattern = '/^\[([A-Za-z0-9]{1,10})\](.*)$/';
    $matches = array();
    $parsedExclusions = array();


    foreach($exclusions as $exclusion) {
      if(preg_match($exclusionPattern, $exclusion, $matches) > 0) {
        array_push($parsedExclusions, new WideAngleExclusion($matches[1], $matches[2]));
      }
    }
    return $parsedExclusions;
  }

  function parseRequestExclusionPaths($request) {
    $pattern = "/^waa_exc_path_(\d{1,2})_type$/";
    $exclusions = array();
    foreach($request as $key => $exclusionType) {
      $idx = array();
      if(preg_match($pattern, $key, $idx)) {
        $valueKey = "waa_exc_path_".$idx[1]."_value";
        $exclusionValue = trim($request[$valueKey]);
        if($exclusionValue != null && $exclusionValue != "") {
          $typedExclusion = "[" . $exclusionType . "]" .$exclusionValue;
          array_push($exclusions, $typedExclusion);
        }
      }
    }
    return $exclusions;
  }
}

?>