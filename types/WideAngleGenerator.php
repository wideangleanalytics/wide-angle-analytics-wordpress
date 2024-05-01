<?php

class WideAngleGenerator {
  public $siteId;
  public $ignoreHash;
  public $trackerDomain;
  public $exclusionPaths;
  public $includeParams;
  public $ePrivacyMode;

  public function __construct($attributes) {
    $this->siteId           = $attributes['site_id'];
    $this->trackerDomain    = $attributes['tracker_domain'];
    $this->ignoreHash       = $attributes['ignore_hash'];
    $this->exclusionPaths   = $attributes['exclusion_paths'];
    $this->includeParams    = $attributes['include_params'];
    $this->fingerprint      = $attributes['fingerprint'];
    $this->supressDnt       = $attributes['supress_dnt'];
  }


  function generateHeaderScript() {
    $href = esc_attr("https://{$this->trackerDomain}/script/{$this->siteId}.js");
    $script = <<<EOD
<link href="{$href}" rel="prefetch"/>
EOD;
    return $script;
  }

  function generateFooterScript() {
    $trackerUrlAttribute    = esc_attr("https://{$this->trackerDomain}/script/{$this->siteId}.js");
    $pathExlusionsAttribute = $this->exclusionPaths != ''       ? "data-waa-exc-paths=\""     . esc_attr($this->exclusionPaths) . "\"": '';
    $includeParamsAttribute = $this->includeParams  != ''       ? "data-waa-inc-params=\""    . esc_attr($this->includeParams)  . "\"": '';
    $ignoreHashAttribute    = $this->ignoreHash != ''           ? "data-waa-ignore-hash=\""   . esc_attr($this->ignoreHash)     . "\"": 'data-waa-ignore-hash="false"';
    $fingerprintAttribute   = $this->fingerprint != ''          ? "data-waa-fingerprint=\""   . esc_attr($this->fingerprint)    . "\"": '';
    $supressDntAttribute    = $this->supressDnt != ''           ? "data-waa-dnt-supress=\""   . esc_attr($this->supressDnt)     . "\"": 'data-waa-dnt-supress="false"';
    $script = <<<EOD
<script async defer
  src="{$trackerUrlAttribute}"
  $fingerprintAttribute
  $ignoreHashAttribute
  $supressDntAttribute
  $includeParamsAttribute
  $pathExlusionsAttribute></script>
EOD;
    return preg_replace("/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/", "\n", $script);
  }

}

?>