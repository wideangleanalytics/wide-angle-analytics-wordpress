<?php
/*
  Plugin Name:          Wide Angle Analytics
  Plugin URI:           https://wordpress.org/plugins/wide-angle-analytics/
  Description:          Easily enable and configure Wide Angle Analytics on your Wordpress site
  Author:               Wide Angle Analytics by Input Objects GmbH
  Author URI:           https://wideangle.co
  Version:              1.0.6
  Requires at least:    5.2
  Requires PHP:         7.2
  License:              GPL v2
  License URI:          https://www.gnu.org/licenses/gpl-2.0.html
*/
?>
<?php
class WideAngleAnalytics {

  const WAA_CONF_SITE_ID                  = "waa_site_id";
  const WAA_CONF_TRACKER_DOMAIN           = "waa_tracker_domain";
  const WAA_CONF_FINGERPRINT              = "waa_fingerprint";
  const WAA_CONF_EPRIVACY_MODE            = "waa_eprivacy_mode";
  const WAA_CONF_EXC_PATHS                = "waa_exc_path";
  const WAA_CONF_INC_PARAMS               = "waa_inc_params";
  const WAA_CONF_IGNORE_HASH              = "waa_ignore_hash";
  const WAA_CONF_ATTRIBUTES               = "waa_attributes";

  public function __construct() {
    $this->plugin = new stdClass;
    $this->plugin->name = 'wide-angle-analytics';
    $this->plugin->displayName = 'Wide Angle Analytics';
    $this->plugin->folder = plugin_dir_path( __FILE__ );
    include_once( $this->plugin->folder . '/types/WideAngleHelpers.php' );
    include_once( $this->plugin->folder . '/types/WideAngleGenerator.php' );

    $this->plugin->helpers = new WideAngleHelpers();
    $this->plugin->exclusionTypes = array(
      "start" => "Starts with",
      "end" => "Ends with",
      "regex" => "RegEx",
    );

    $this->plugin->ePrivacyModes = array(
      "disabled" => "Disable Tracking",
      "consent" => "Track assuming consent"
    );

    add_action('admin_init', array( &$this, 'registerPluginSettings' ) );
    add_action('admin_menu', array( &$this, 'registerAdminMenu' ));

    $this->plugin->generator = new WideAngleGenerator(get_option(self::WAA_CONF_ATTRIBUTES));
    add_action('wp_head', array( &$this, 'renderHeaderScript'));
    add_action('wp_footer', array( &$this, 'renderFooterScript'));
  }

  /**
   * When script is configured and saved, function will render prefetch script directive.
   * Inteded to be used with 'wp_head' hook.
   */
  function renderHeaderScript() {
    $this->renderContent($this->plugin->generator->generateHeaderScript()); // The Escaping takes place in the WideAngleGenerator::generateHeaderScript method.
  }

  /**
   * When script is configured and saved, function will render Wide Angle Analytics script.
   * Inteded to be used with 'wp_footer' hook.
   */
  function renderFooterScript() {
    $this->renderContent($this->plugin->generator->generateFooterScript()); // The Escaping takes place in the WideAngleGenerator::generateFooterScript method.
  }

  /**
   * Helper function to render provided content "as-is" when rendering public facing page.
   *
   * The $content is already escaped.
   */
  private function renderContent($content) {
    if (
      is_admin() ||
      is_feed() ||
      is_robots() ||
      is_trackback()) {
      return;
    }
    if(trim($content) === "") {
      return;
    }
    echo wp_unslash($content);
  }

  /**
   * Adds Wide Angle Analytics configuration menu to Admin "Settings" menu.
   */
  function registerAdminMenu() {
    add_submenu_page(
      'options-general.php',
      $this->plugin->displayName,
      $this->plugin->displayName,
      'manage_options',
      $this->plugin->name,
      array( &$this, 'adminPanelHandler' )
    );
  }

  /**
   * Handle configuration submission.
   *
   * When successful, the generated script will rendered and storedin plugin keyed setting.
   * This setting will be subsequently used when time comes to render it. The intermediate settings
   * are parsed and processed only during configuration change.
   *
   */
  function adminPanelHandler() {
    if ( ! current_user_can( 'manage_options' ) ) {
      wp_die( __( 'Insufficient permissions. You are not allows to modify setting.', $this->plugin->displayName ) );
    }
    if ( isset( $_REQUEST['submit'] ) ) {
      if ( ! isset( $_REQUEST[ $this->plugin->name . '_nonce' ] ) ) {
        $this->errorMessage = __( 'Nonce field is missing. Settings NOT saved.',$this->plugin->name );
      } elseif ( ! wp_verify_nonce( $_REQUEST[ $this->plugin->name . '_nonce' ], $this->plugin->name ) ) {
        $this->errorMessage = __( 'Invalid nonce specified. Settings NOT saved.', $this->plugin->name );
      } else {
        $waaSiteId          = $this->plugin->helpers->validateSiteId(self::WAA_CONF_SITE_ID, sanitize_text_field($_REQUEST['waa_site_id']));
        $waaTrackerDomain   = $this->plugin->helpers->validateTrackerDomain(self::WAA_CONF_TRACKER_DOMAIN, sanitize_text_field($_REQUEST['waa_tracker_domain']));
        $waaIgnoreHash      = $this->plugin->helpers->validateIgnoreHashFlag(self::WAA_CONF_IGNORE_HASH, sanitize_text_field($_REQUEST['waa_ignore_hash']));
        $waaFingerprint     = $this->plugin->helpers->validateFingerprint(self::WAA_CONF_FINGERPRINT, sanitize_text_field($_REQUEST['waa_fingerprint']));
        $waaEPrivacyMode    = $this->plugin->helpers->validateEPrivacyMode(self::WAA_CONF_EPRIVACY_MODE, sanitize_text_field($_REQUEST['waa_eprivacy_mode']));
        $waaIncParams       = $this->plugin->helpers->validateIncludeParams(self::WAA_CONF_INC_PARAMS, $_REQUEST);
        $waaExclusionPaths  = $this->plugin->helpers->validateExclusionPathsRequest(self::WAA_CONF_EXC_PATHS, $_REQUEST);

        include_once( $this->plugin->folder . '/types/WideAngleAttributes.php');
        $merged = array($waaSiteId, $waaTrackerDomain, $waaIgnoreHash, $waaIncParams, $waaExclusionPaths);
        $errors = array();
        foreach($merged as $validated) {
          if(!$validated->is_valid()) {
            array_push($errors, $validated->get_error());
          }
        }

        if(count($errors) === 0) {
          $attributes = new WideAngleAttributes(
            $waaSiteId->get_value(),
            $waaTrackerDomain->get_value(),
            $waaIgnoreHash->get_value(),
            $waaExclusionPaths->get_value(),
            $waaIncParams->get_value(),
            $waaFingerprint->get_value(),
            $waaEPrivacyMode->get_value()
          );
          update_option(self::WAA_CONF_SITE_ID,         $waaSiteId->get_value());
          update_option(self::WAA_CONF_TRACKER_DOMAIN,  $waaTrackerDomain->get_value());
          update_option(self::WAA_CONF_IGNORE_HASH,     $waaIgnoreHash->get_value());
          update_option(self::WAA_CONF_EXC_PATHS,       $waaExclusionPaths->get_value());
          update_option(self::WAA_CONF_INC_PARAMS,      $waaIncParams->get_value());
          update_option(self::WAA_CONF_FINGERPRINT,     $waaFingerprint->get_value());
          update_option(self::WAA_CONF_EPRIVACY_MODE,   $waaEPrivacyMode->get_value());
          update_option(self::WAA_CONF_ATTRIBUTES,      $attributes->generateAttributes());
          $this->message = __('Settings updated', $this->plugin->name);
        } else {
          $this->errorMessage = $errors;
        }
      }
    }
    $this->settings = array(
      self::WAA_CONF_SITE_ID                  => get_option(self::WAA_CONF_SITE_ID),
      self::WAA_CONF_EXC_PATHS                => get_option(self::WAA_CONF_EXC_PATHS),
      self::WAA_CONF_INC_PARAMS               => get_option(self::WAA_CONF_INC_PARAMS),
      self::WAA_CONF_TRACKER_DOMAIN           => get_option(self::WAA_CONF_TRACKER_DOMAIN),
      self::WAA_CONF_IGNORE_HASH              => get_option(self::WAA_CONF_IGNORE_HASH),
      self::WAA_CONF_FINGERPRINT              => get_option(self::WAA_CONF_FINGERPRINT),
      self::WAA_CONF_EPRIVACY_MODE            => get_option(self::WAA_CONF_EPRIVACY_MODE),
      self::WAA_CONF_ATTRIBUTES               => get_option(self::WAA_CONF_ATTRIBUTES)
    );
    include_once( $this->plugin->folder . '/views/admin_settings.php' );
  }

  /**
   * Creates settings for Wide Angle Analytics plugin.
   *
   * Following settings are registered:
   *  - waa_site_id
   *  - waa_tracker_domain
   *  - waa_exc_path
   *  - waa_inc_params
   *  - waa_ignore_hash
   *  - waa_header_script
   *  - waa_footer_script
   */
  function registerPluginSettings() {
    register_setting($this->plugin->name, self::WAA_CONF_SITE_ID);
    register_setting($this->plugin->name, self::WAA_CONF_EXC_PATHS);
    register_setting($this->plugin->name, self::WAA_CONF_INC_PARAMS);
    register_setting($this->plugin->name, self::WAA_CONF_TRACKER_DOMAIN, array('default' => 'stats.wideangle.co'));
    register_setting($this->plugin->name, self::WAA_CONF_IGNORE_HASH, array('default' => 'false'));
    register_setting($this->plugin->name, self::WAA_CONF_FINGERPRINT, array('default' => 'false'));
    register_setting($this->plugin->name, self::WAA_CONF_EPRIVACY_MODE, array('default' => 'disabled'));
    register_setting($this->plugin->name, self::WAA_CONF_ATTRIBUTES, array('type' => 'array'));
  }

}

$waa = new WideAngleAnalytics(); // Plugin is initialized on contruction.
?>
