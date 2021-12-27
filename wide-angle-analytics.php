<?php
/*
  Plugin Name:          Wide Angle Analytics
  Plugin URI:           https://wordpress.org/plugins/wide-angle-analytics/
  Description:          Easily enable and configure Wide Angle Analytics on your Wordpress site
  Author:               Wide Angle Analytics by Input Objects GmbH
  Author URI:           https://wideangle.co
  Version:              1.0.0
  Requires at least:    5.2
  Requires PHP:         7.2
  License:              GPL v2
  License URI:          https://www.gnu.org/licenses/gpl-2.0.html
*/
?>
<?php
class WideAngleAnalytics {

  const WAA_SEPARTOR                      = "|:";
  const WAA_CONF_SITE_ID                  = "waa_site_id";
  const WAA_CONF_TRACKER_DOMAIN           = "waa_tracker_domain";
  const WAA_CONF_EXC_PATHS                = "waa_exc_path";
  const WAA_CONF_INC_PARAMS               = "waa_inc_params";
  const WAA_CONF_IGNORE_HASH              = "waa_ignore_hash";
  const WAA_CONF_GENERATED_HEADER_SCRIPT  = "waa_header_script";
  const WAA_CONF_GENERATED_FOOTER_SCRIPT  = "waa_footer_script";

  public function __construct() {
    $this->plugin = new stdClass;
    $this->plugin->name = 'wide-angle-analytics';
    $this->plugin->displayName = 'Wide Angle Analytics';
    $this->plugin->folder = plugin_dir_path( __FILE__ );
    include_once( $this->plugin->folder . '/types/WideAngleHelpers.php' );

    $this->plugin->helpers = new WideAngleHelpers();
    $this->plugin->exclusionTypes = array(
      "start" => "Starts with",
      "end" => "Ends with",
      "regex" => "RegEx",
    );

    add_action( 'admin_init', array( &$this, 'registerPluginSettings' ) );
    add_action( 'admin_menu', array( &$this, 'registerAdminMenu' ));
    add_action('wp_head', array( &$this, 'renderHeaderScript'));
    add_action('wp_footer', array( &$this, 'renderFooterScript'));
  }

  /**
   * When script is configured and saved, function will render prefetch script directive.
   * Inteded to be used with 'wp_head' hook.
   */
  function renderHeaderScript() {
    $this->renderSetting(self::WAA_CONF_GENERATED_HEADER_SCRIPT);
  }

  /**
   * When script is configured and saved, function will render Wide Angle Analytics script.
   * Inteded to be used with 'wp_footer' hook.
   */
  function renderFooterScript() {
    $this->renderSetting(self::WAA_CONF_GENERATED_FOOTER_SCRIPT);
  }

  /**
   * When called, renders content of named setting.
   * Rendering is omitted if current view is: Admin, Feed, Robots or Trackback.
   */
  function renderSetting($setting) {
    if (
      is_admin() ||
      is_feed() ||
      is_robots() ||
      is_trackback()) {
      return;
    }

    $script = get_option($setting);
    if(trim($script) === "") {
      return;
    }
    echo wp_unslash($script);
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
        $waaSiteId = $_REQUEST['waa_site_id'];
        $waaTrackerDomain = $this->plugin->helpers->normalizeTrackerDomain(trim($_REQUEST['waa_tracker_domain']));
        $waaIgnoreHash = $this->plugin->helpers->normalizeBoolean($_REQUEST['waa_ignore_hash']);
        $waaExclusionPaths = implode(self::WAA_SEPARTOR, $this->plugin->helpers->parseRequestExclusionPaths($_REQUEST));
        $waaIncParams = implode(self::WAA_SEPARTOR, $this->plugin->helpers->parseRequestIncludeParams($_REQUEST));

        include_once( $this->plugin->folder . '/types/WideAngleConfig.php' );
        $config = new WideAngleConfig($waaSiteId, $waaTrackerDomain, $waaIgnoreHash, $waaExclusionPaths, $waaIncParams);

        update_option(self::WAA_CONF_GENERATED_FOOTER_SCRIPT, $config->generateFooterScript());
        update_option(self::WAA_CONF_GENERATED_HEADER_SCRIPT, $config->generateHeaderScript());
        update_option(self::WAA_CONF_SITE_ID, $waaSiteId );
        update_option(self::WAA_CONF_TRACKER_DOMAIN, $waaTrackerDomain);
        update_option(self::WAA_CONF_IGNORE_HASH, $waaIgnoreHash );
        update_option(self::WAA_CONF_EXC_PATHS, $waaExclusionPaths );
        update_option(self::WAA_CONF_INC_PARAMS, $waaIncParams);
      }
    }
    $this->settings = array(
      self::WAA_CONF_SITE_ID  => esc_html(get_option( self::WAA_CONF_SITE_ID)),
      self::WAA_CONF_EXC_PATHS => esc_html(get_option( self::WAA_CONF_EXC_PATHS)),
      self::WAA_CONF_INC_PARAMS => esc_html(get_option( self::WAA_CONF_INC_PARAMS)),
      self::WAA_CONF_TRACKER_DOMAIN => esc_html(get_option( self::WAA_CONF_TRACKER_DOMAIN)),
      self::WAA_CONF_IGNORE_HASH => esc_html(get_option( self::WAA_CONF_IGNORE_HASH)),
      self::WAA_CONF_GENERATED_HEADER_SCRIPT => esc_html(get_option( self::WAA_CONF_GENERATED_HEADER_SCRIPT )),
      self::WAA_CONF_GENERATED_FOOTER_SCRIPT => esc_html(get_option( self::WAA_CONF_GENERATED_FOOTER_SCRIPT )),
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
    register_setting( $this->plugin->name, self::WAA_CONF_SITE_ID, array(
      'sanitize_callback' => 'trim',
    ) );
    register_setting( $this->plugin->name, self::WAA_CONF_EXC_PATHS, array(
      'sanitize_callback' => 'trim',
    ) );
    register_setting( $this->plugin->name, self::WAA_CONF_INC_PARAMS, array(
      'sanitize_callback' =>'trim',
      'default' => ''
    ) );
    register_setting( $this->plugin->name, self::WAA_CONF_TRACKER_DOMAIN, array(
      'sanitize_callback' => array(&$this->plugin->helpers, 'normalizeTrackerDomain'),
      'default' => 'https://stats.wideangle.co'
    ) );
    register_setting( $this->plugin->name, self::WAA_CONF_IGNORE_HASH, array(
      'sanitize_callback' => array(&$this->plugin->helpers, 'normalizeBoolean'),
      'default' => 'false'
    ) );
    register_setting( $this->plugin->name, self::WAA_CONF_GENERATED_HEADER_SCRIPT, array(
      'sanitize_callback' => 'trim',
      'default' => ''
    ) );
    register_setting( $this->plugin->name, self::WAA_CONF_GENERATED_FOOTER_SCRIPT, array(
      'sanitize_callback' => 'trim',
      'default' => ''
    ) );
  }

}

$waa = new WideAngleAnalytics(); // Plugin is initialized on contruction.
?>
