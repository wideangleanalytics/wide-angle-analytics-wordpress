<?php
include_once( $this->plugin->folder . '/types/WideAngleExclusion.php' );
include_once( $this->plugin->folder . '/types/WideAngleValidated.php' );

class WideAngleHelpers {
  const WAA_SEPARTOR                            = "|:";
  private const includeParamRequestKeyPattern   = "/^waa_inc_params_(\d{1,2})$/";
  private const includeParamRequestValuePattern = "/^[A-Za-z0-9_-]{1,128}$/";
  private const excludePathRequestKeyPattern    = "/^waa_exc_path_(\d{1,2})_type$/";

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

  function validateIgnoreHashFlag($name, $ignoreHash) {
    if(filter_var($ignoreHash, FILTER_VALIDATE_BOOLEAN)) {
      return WideAngleValidated::createValid($name, $ignoreHash, "true");
    } else {
      return WideAngleValidated::createValid($name, $ignoreHash, "false");
    }
  }

  function validateFingerprint($name, $fingerprint) {
    if(filter_var($fingerprint, FILTER_VALIDATE_BOOLEAN)) {
      return WideAngleValidated::createValid($name, $fingerprint, "true");
    } else {
      return WideAngleValidated::createValid($name, $fingerprint, "false");
    }
  }

  function validateSiteId($name, $siteId) {
    if(preg_match("/^[a-zA-Z0-9]{10,24}$/", $siteId)) {
      return WideAngleValidated::createValid($name, $siteId, strtoupper(trim($siteId)));
    } else {
      return WideAngleValidated::createInvalid($name, $siteId, "Site ID is expected to consist only letters and digits. It must be at least 10 character long and no longer than 24.");
    }
  }

  function validateTrackerDomain($name, $domain) {
    if(filter_var($domain, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME)) {
      return WideAngleValidated::createValid($name, $domain, $domain);
    } else {
      return WideAngleValidated::createInvalid($name, $domain, "The tracked domain must be a valid domain name.");
    }
  }


  function validateIncludeParams($name, $request) {
    $params = array();
    foreach($request as $requestKey => $paramValue) {
      if(preg_match(self::includeParamRequestKeyPattern, $requestKey)) {
        $sanitizedValue = sanitize_text_field($paramValue);
        if(preg_match(self::includeParamRequestValuePattern, $sanitizedValue)) {
          array_push($params, trim($sanitizedValue));
        } else {
          return WideAngleValidated::createInvalid($name, $sanitizedValue, "Name of parameter to include in request must consint of letters, numbers and can contain _ or - sign only.");
        }
      }
    }
    return WideAngleValidated::createValid($name, $params, implode(self::WAA_SEPARTOR, $params));
  }


  function validateExclusionPathsRequest($name, $request) {
    $exclusions = array();
    foreach($request as $key => $exclusionType) {
      $idx = array();
      if(preg_match(self::excludePathRequestKeyPattern, $key, $idx)) {
        $valueKey = "waa_exc_path_".$idx[1]."_value";
        $sanitizedValue = trim(sanitize_text_field($request[$valueKey]));
        if($sanitizedValue != null) {
          if(filter_var($sanitizedValue, FILTER_VALIDATE_REGEXP)) {
            $typedExclusion = "[" . $exclusionType . "]" . $sanitizedValue;
            array_push($exclusions, $typedExclusion);
          } else {
            $typedExclusion = "[" . $exclusionType . "]" . filter_var($sanitizedValue, FILTER_SANITIZE_SPECIAL_CHARS);
            array_push($exclusions, $typedExclusion);
          }
        }
      }
    }
    return WideAngleValidated::createValid($name, implode(self::WAA_SEPARTOR, $exclusions), null);
  }

  function parseIncludeParamsSetting($params) {
    if(trim($params) !== "") {
      return  preg_split("/\|\:/", $params);
    } else {
      return array();
    }
  }

  function parseExclusionSetting($path) {
    $flatExclusions     = $path;
    $exclusions         = preg_split("/\|\:/", $flatExclusions);
    $exclusionPattern   = '/^\[([A-Za-z0-9]{1,10})\](.*)$/';
    $matches            = array();
    $parsedExclusions   = array();


    foreach($exclusions as $exclusion) {
      if(preg_match($exclusionPattern, $exclusion, $matches) > 0) {
        array_push($parsedExclusions, new WideAngleExclusion($matches[1], $matches[2]));
      }
    }
    return $parsedExclusions;
  }

}

?>