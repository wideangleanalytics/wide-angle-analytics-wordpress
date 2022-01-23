<?php
$siteId               = wp_unslash($this->settings[self::WAA_CONF_SITE_ID]);
$trackerDomain        = wp_unslash($this->settings[self::WAA_CONF_TRACKER_DOMAIN]);
$ignoreHash           = filter_var($this->settings[self::WAA_CONF_IGNORE_HASH], FILTER_VALIDATE_BOOLEAN);
$fingerprint          = filter_var($this->settings[self::WAA_CONF_FINGERPRINT], FILTER_VALIDATE_BOOLEAN);
$ePrivacyMode         = wp_unslash($this->settings[self::WAA_CONF_EPRIVACY_MODE]);
$parsedExclusions     = $this->plugin->helpers->parseExclusionSetting(wp_unslash($this->settings[self::WAA_CONF_EXC_PATHS]));
$parsedIncludeParams  = $this->plugin->helpers->parseIncludeParamsSetting(wp_unslash($this->settings[self::WAA_CONF_INC_PARAMS]));
$generator            = new WideAngleGenerator($this->settings[self::WAA_CONF_ATTRIBUTES]);
?>
<div class="wrap">
  <h2>
    <?php echo $this->plugin->displayName; ?> &raquo; <?php esc_html( 'Settings', 'wide-angle-analytics' ); ?>
  </h2>

  <div>
    <h3>Configure your Wide Angle Analytics tracker script</h3>
    <details>
      <summary>Need help?</summary>
      <p>Our <a href="https://wideangle.co/documentation">documentation</a> has dedicated section about:
        <ul class="ul-disc">
          <li><a href="https://wideangle.co/documentation/create-and-configure-site" target="_blank">Creating New Site</a></li>
          <li><a href="https://wideangle.co/documentation/configure-site" target="_blank">Site Configuration and Meaning of Specific Options</a></li>
          <li><a href="https://wideangle.co/documentation/track-with-custom-domain" target="_blank">Creating Custom Tracker Domain</a></li>
        </ul>
      </p>
    </details>
  </div>

  <?php
  if ( isset( $this->message ) ) {
  ?>
    <div class="updated fade"><p><?php echo esc_html($this->message); ?></p></div>
  <?php
  }
  if ( isset( $this->errorMessage ) ) {
    if(is_array($this->errorMessage)) {
      foreach($this->errorMessage as $error) {
        ?>
    <div class="error fade"><p><?php echo esc_html($error); ?></p></div>
        <?php
      }
    }
    else {
  ?>
    <div class="error fade"><p><?php echo esc_html($this->errorMessage); ?></p></div>
  <?php
    }
  }
  ?>
  <div>
    <form action="options-general.php?page=<?php echo esc_attr($this->plugin->name); ?>" method="post">
      <table class="form-table" role="presentation">
        <tbody>
          <tr>
            <th scope="row"><label>Site ID</label></th>
            <td>
              <input id="waa_site_id" type="text" name="waa_site_id" class="regular-text" value="<?php echo esc_attr($siteId); ?>"/>
              <p class="description" id="tagline-description">A Site ID. You will find it in the Site Settings, in Wide Angle Analytics Dashboard.</p>
            </td>
          </tr>
          <tr>
            <th scope="row"><label>Tracker Domain</label></th>
            <td>
              <input id="waa_tracker_domain" type="text" name="waa_tracker_domain" class="regular-text code" value="<?php echo esc_attr($trackerDomain); ?>"/>
              <p class="description" id="tagline-description">A domain you selected for your tracker. You can check current domain in the Site Settings, in the Wide Angle Analytics. If you haven't set custom domain for your site, there is no need to change this field.</p>
            </td>
          </tr>
          <tr>
            <th scope="row"><label>Excluded Paths</label></th>
            <td>
              <div data-waa-exc-path="exclusion_container">
                <?php
                for($i = 0; $i < sizeof($parsedExclusions); $i++) {
                  $exclusion = $parsedExclusions[$i];
                ?>
                <div data-waa-exc-path="<?php echo esc_attr($i); ?>" style="display: flex; flex-direction: row; margin-bottom: 0.3rem">
                  <select name="waa_exc_path_<?php echo esc_attr($i); ?>_type" id="waa_exc_path_<?php echo esc_attr($i); ?>_type">
                    <?php
                      foreach($this->plugin->exclusionTypes as $id => $label) {
                    ?>
                      <option value="<?php echo esc_attr($id); ?>"<?php if($exclusion->get_type() == $id) echo ' selected'; ?>><?php echo esc_html($label); ?></option>
                    <?php
                      }
                    ?>
                  </select>
                  <input type="text" name="waa_exc_path_<?php echo esc_attr($i); ?>_value" value="<?php echo esc_attr($exclusion->get_value()); ?>"/>
                  <button data-waa-action="remove_exclusion" data-waa-exc-path="<?php echo esc_attr($i); ?>" class="button button-secondary">Remove</button>
                </div>
                <?php
                }
                ?>
              </div>
              <button id="add_exclusion" class="button button-secondary" style="margin-top: 0.5rem;">Add</button>
              <p class="description" id="tagline-description">A list of URL patterns you would like to exclude from tracking.</p>
            </td>
          </tr>
          <tr>
            <th scope="row"><label>List of additional parameters</label></th>
            <td>
              <div data-waa-inc-params="params_container">
                <?php
                for($i = 0; $i < sizeof($parsedIncludeParams); $i++) {
                  $param = $parsedIncludeParams[$i];
                ?>
                <div data-waa-inc-params="<?php echo esc_attr($i); ?>" style="display: flex; flex-direction: row; margin-bottom: 0.3rem">
                  <input type="text" name="waa_inc_params_<?php echo esc_attr($i); ?>" value="<?php echo esc_attr($param); ?>"/>
                  <button data-waa-action="remove_param" data-waa-inc-params="<?php echo esc_attr($i); ?>" class="button button-secondary">Remove</button>
                </div>
                <?php
                }
                ?>
              </div>
              <button id="add_param" class="button button-secondary" style="margin-top: 0.5rem;">Add</button>
              <p class="description" id="tagline-description">Add parameter to include when sending tracking event. By default only <code>utm_*</code> and <code>ref</code> parameters are transmitted.</p>
            </td>
          </tr>
          <tr>
            <th scope="row"><label>URL Fragment</label></th>
            <td>
              <fieldset>
                <legend class="screen-reader-text"><span>URL Fragment</span></legend>
                <label>
                  <input id="waa_ignore_hash" type="checkbox" name="waa_ignore_hash" <?php if($ignoreHash) { echo "checked"; } ?>/> Ignore
                </label>
              </fieldset>
              <p class="description" id="tagline-description">By default, w URL Fragment/hash is trasmitted as part of the tracking event. You can disable this behaviour. The fragment will be stripped before sending an event.</p>
            </td>
          </tr>
          <tr>
            <th scope="row"><label>Browser Fingerprinting</label></th>
            <td>
              <fieldset>
                <legend class="screen-reader-text"><span>Browser Fingerprinting</span></legend>
                <label>
                  <input id="waa_fingerprint" type="checkbox" name="waa_fingerprint" <?php if($fingerprint) { echo "checked"; } ?>/> Enable
                </label>
              </fieldset>
              <p class="description" id="tagline-description">The tracker script will <b>not</b> attempt to fingerprint the browser by default. You can improve tracking qaulity by enabling more reliable browser fingerprinting. Enabling this feature might require collecting consent.</p>
            </td>
          </tr>
          <tr>
            <th scope="row"><label>ePrivacyMode</label></th>
            <td>
              <fieldset>
                <legend class="screen-reader-text"><span>Browser Fingerprinting</span></legend>
                <label>
                  <select name="waa_eprivacy_mode" id="waa_eprivacy_mode">
                      <?php
                        foreach($this->plugin->ePrivacyModes as $id => $label) {
                      ?>
                        <option value="<?php echo esc_attr($id); ?>"<?php if($ePrivacyMode == $id) echo ' selected'; ?>><?php echo esc_html($label); ?></option>
                      <?php
                        }
                      ?>
                    </select>
                </label>
              </fieldset>
              <p class="description" id="tagline-description">When you disable tracking, the script collects only bare-bone information. You can opt for more verbose tracking, but be aware that according to ePrivacy Regulations, this might require visitor's consent.</p>
            </td>
          </tr>
        </tbody>
      </table>
      <?php wp_nonce_field( $this->plugin->name, $this->plugin->name . '_nonce' ); ?>
      <p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="Save Changes" style="margin-right: 1rem;">Press save to generate and preview new script.
      </p>
    </form>
    <div>
      <h3>Script generated from saved settings</h3>
<code style="background-color: inherit">
<pre style="padding: 1rem; border: 1px solid;">
&lt;head&gt;
&lt;!-- .. --&gt;
<b><?php echo esc_html($generator->generateHeaderScript()); ?></b>

&lt;/head&gt;
&lt;!-- .. --&gt;
<b><?php echo esc_html($generator->generateFooterScript()); ?></b>
</pre>
</code>
    </div>
  </div>
</div>
<script>

  // Exlude Params helpers

  var pathExclusionTemplate = '<div data-waa-exc-path="0" style="display: flex; flex-direction: row; margin-bottom: 0.3rem"><select name="waa_exc_path_0_type"><option value="start">Starts with</option><option value="end">Ends with</option><option value="regex">RegEx</option></select><input type="text" name="waa_exc_path_0_value"/><button data-waa-action="remove_exclusion" data-waa-exc-path="0" class="button button-secondary">Remove</button></div>'

  function addNewExclusion(seq) {
    var element = jQuery("div[data-waa-exc-path] div:last-child");
    var idx = 0;
    if (element == undefined && seq) {
      idx = seq;
    } else if (element) {
      lastKnownIdx = element.attr('data-waa-exc-path');
      idx = lastKnownIdx ? parseInt(lastKnownIdx) + 1 : 0;
    }
    var elementTemplate = jQuery.parseHTML(pathExclusionTemplate)
    var copy = jQuery(elementTemplate);

    copy.attr('data-waa-exc-path', idx)
    copy.find('select').attr('name', 'waa_exc_path_' + idx + '_type');
    copy.find('select').val("start");
    copy.find('input').attr('name', 'waa_exc_path_' + idx + '_value');
    copy.find('input').val("");
    copy.find('button').attr('data-waa-exc-path', idx);
    if(idx > 0) {
      copy.insertAfter("div[data-waa-exc-path] div:last-child")
    } else {
      copy.appendTo("div[data-waa-exc-path]")
    }
  }

  function removeExclusion(idx) {
    var parent = jQuery("div[data-waa-exc-path]");
    jQuery(document).find('div[data-waa-exc-path="' + idx + '"]').remove();
    if(parent.children().length == 0) {
      addNewExclusion(0);
    }
  }

  jQuery(document).on('click', "#add_exclusion", function(event) {
    event.preventDefault();
    addNewExclusion();
  });

  jQuery(document).on('click','button[data-waa-action="remove_exclusion"]', function(event) {
    event.preventDefault();
    var idx = event.currentTarget.getAttribute('data-waa-exc-path');
    removeExclusion(idx);
    return false;
  });

  // Include Params helpers

  var paramIncludeTemplate = '<div data-waa-inc-params="0" style="display: flex; flex-direction: row; margin-bottom: 0.3rem"><input type="text" name="waa_inc_params_0" value="" pattern="[A-Za-z0-9_-]{1,128}"/><button data-waa-action="remove_param" data-waa-inc-params="0" class="button button-secondary">Remove</button></div>';

  function addNewParam(seq) {
    var element = jQuery("div[data-waa-inc-params] div:last-child");
    var idx = 0;
    if (element == undefined && seq) {
      idx = seq;
    } else if (element) {
      lastKnownIdx = element.attr('data-waa-inc-params');
      idx = lastKnownIdx ? parseInt(lastKnownIdx) + 1 : 0;
    }
    var elementTemplate = jQuery.parseHTML(paramIncludeTemplate)
    var copy = jQuery(elementTemplate);

    copy.attr('data-waa-inc-params', idx)
    copy.find('input').attr('name', 'waa_inc_params_' + idx);
    copy.find('input').val("");
    copy.find('button').attr('data-waa-inc-params', idx);
    if(idx > 0) {
      copy.insertAfter("div[data-waa-inc-params] div:last-child")
    } else {
      copy.appendTo("div[data-waa-inc-params]")
    }
  }

  function removeParam(idx) {
    var parent = jQuery("div[data-waa-inc-params]");
    console.log('Removing div[data-waa-inc-params="' + idx + '"]');
    jQuery(document).find('div[data-waa-inc-params="' + idx + '"]').remove();
    if(parent.children().length == 0) {
      addNewParam(0);
    }
  }

  jQuery(document).on('click', "#add_param", function(event) {
    event.preventDefault();
    addNewParam();
  });

  jQuery(document).on('click','button[data-waa-action="remove_param"]', function(event) {
    event.preventDefault();
    var idx = event.currentTarget.getAttribute('data-waa-inc-params');
    removeParam(idx);
    return false;
  });

</script>