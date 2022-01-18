<?php
class WideAngleAttributes {
  public $siteId;
  public $ignoreHash;
  public $fingerprint;
  public $trackerDomain;
  public $exclusionString;
  public $includeParamsString;
  private $helpers;

  public function __construct($siteId, $trackerDomain, $ignoreHash, $exclusionString, $includeParamsString, $fingerprint) {
    $this->siteId = $siteId;
    $this->trackerDomain = $trackerDomain;
    $this->ignoreHash = $ignoreHash;
    $this->exclusionString = $exclusionString;
    $this->includeParamsString = $includeParamsString;
    $this->fingerprint = $fingerprint;
    $this->helpers = new WideAngleHelpers();
  }

  public function generateAttributes() {
    return array(
      'site_id' => $this->siteId,
      'tracker_domain' => $this->trackerDomain,
      'ignore_hash' => $this->ignoreHash,
      'fingerprint' => $this->fingerprint,
      'exclusion_paths' => $this->generateExclusionsAttribute(),
      'include_params' => $this->generateIncludeParamsAttribute()
    );
  }


  private function generateIncludeParamsAttribute() {
    $params = $this->helpers->parseIncludeParamsSetting($this->includeParamsString);
    return implode(",", $params);
  }

  private function generateExclusionsAttribute() {
    $pathExlusionsAttribute = "";
    $exclusions = $this->helpers->parseExclusionSetting($this->exclusionString);
    if(sizeof($exclusions) > 0) {
      $pathExlusionsAttribute = $this->generateExclusionsAttributeValue($exclusions);
    }

    return $pathExlusionsAttribute;
  }

  private function generateExclusionsAttributeValue($exclusions) {
    $accumulator = array();
    foreach($exclusions as $exclusion) {
      switch($exclusion->get_type()) {
        case "start":
          array_push($accumulator, "^" .  preg_quote($exclusion->get_value()) . ".*");
          break;
        case "end":
          array_push($accumulator, ".*" .  preg_quote($exclusion->get_value()) . "$");
          break;
        case "regex":
          array_push($accumulator, $exclusion->get_value());
          break;
      }
    }
    return implode(",", $accumulator);
  }
}
?>