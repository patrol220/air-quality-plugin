<?php
function pk_aqp_display_error($message, $type) {
    ?>
    <div id="setting-error-settings_updated" class="<?=$type?> settings-error notice is-dismissible">
        <p><strong><?=$message?></strong></p>
        <button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button>
    </div>
    <?php
}

//Options code, displays in admin section
function pk_aqp_options_code() {
    $user = wp_get_current_user();
    $options = array();

    if($_POST['action'] == 'save') {
        $options_values_prev = get_option('pk_aqp_options');
        check_admin_referer('pk_aqp_settings' . $user->ID); //nonce

        if($options_values_prev['user_can_set'] == 1 && current_user_can('read')) {
            $weather_info = isset($_POST['weather-info']) ? true : false;
            update_user_meta($user->ID, 'weather-info', $weather_info);

            if (pk_aqp_longitude_filter($_POST['longitude']) && pk_aqp_latitude_filter($_POST['latitude'])) {
                $longitude = pk_aqp_longitude_filter($_POST['longitude']);
                $latitude = pk_aqp_latitude_filter($_POST['latitude']);
                update_user_meta($user->ID, 'longitude', $longitude);
                update_user_meta($user->ID, 'latitude', $latitude);
            }
            else {
                pk_aqp_display_error(__('Given coordinates are wrong. Please give values in range from -180 to 180 for longitude and from -90 to 90 for latitude', 'air-quality-plugin'), 'error');
            }
        }

        if(current_user_can('manage_options')) {
            if (pk_aqp_longitude_filter($_POST['longitude-default']) && pk_aqp_latitude_filter($_POST['latitude-default'])) {
                $longitude_default = pk_aqp_longitude_filter($_POST['longitude-default']);
                $latitude_default = pk_aqp_latitude_filter($_POST['latitude-default']);
                $options['longitude'] = $longitude_default;
                $options['latitude'] = $latitude_default;
            }
            else {
                pk_aqp_display_error(__('Given coordinates are wrong. Please give values in range from -180 to 180 for longitude and from -90 to 90 for latitude', 'air-quality-plugin'), 'error');
                $options['longitude'] = $options_values_prev['longitude'];
                $options['latitude'] = $options_values_prev['latitude'];
            }
            if (preg_match('/^[-_A-za-z0-9]{0,}$/', $_POST['google-maps-key'])) {
                $google_maps_key = $_POST['google-maps-key'];
                $options['google_maps_key'] = $google_maps_key;
            }
            else {
                pk_aqp_display_error(__('Wrong Google Maps API key was given', 'air-quality-plugin'), 'error');
                $options['google_maps_key'] = $options_values_prev['google_maps_key'];
            }
            if (preg_match('/^[A-za-z0-9]{0,}$/', $_POST['waqi-key'])) {
                $waqi_key = $_POST['waqi-key'];
                $options['waqi_key'] = $waqi_key;
            }
            else {
                pk_aqp_display_error(__('Wrong waqi.info API key was given', 'air-quality-plugin'), 'error');
                $options['waqi_key'] = $options_values_prev['waqi_key'];
            }
            $user_can_set = isset($_POST['user-can-set']) ? true : false;
            $weather_info_default = isset($_POST['weather-info-default']) ? true : false;
            $options['user_can_set'] = $user_can_set;
            $options['weather_info'] = $weather_info_default;
            update_option('pk_aqp_options', $options);
        }
        pk_aqp_display_error(__('Saved options', 'air-quality-plugin'), 'updated');
    }

    $options_values = get_option('pk_aqp_options');

    $longitude_value = get_user_meta($user->ID, 'longitude', true);
    $latitude_value = get_user_meta($user->ID, 'latitude', true);
    $weather_info = get_user_meta($user->ID, 'weather-info', true);

    $google_api_key = $options_values['google_maps_key'];

    $admin_script_localization = array(
        'google_api_error' => __('Something went wrong during retrieving information from google, try again', 'air-quality-plugin'),
        'google_api_authentication_error' => __('There was a problem with your Google Maps API key, probably you do not entered it correctly, or you do not enabled Geocoding API for your key')
    );

    wp_enqueue_style('pk-aqp-admin-style', PK_AQP_DIR_PATH . 'css/style-admin.css', '', PK_AQP_VERSION);
    wp_enqueue_script('pk-aqp-admin-script', PK_AQP_DIR_PATH . 'js/script-admin.js', array('jquery'), PK_AQP_VERSION);
    wp_localize_script('pk-aqp-admin-script', 'pk_aqp_admin_l10n', $admin_script_localization);
    if(!empty($google_api_key)) {
        wp_enqueue_script('pk-aqp-google-maps-script', "https://maps.googleapis.com/maps/api/js?key=$google_api_key&libraries=places&callback=initAutocomplete", array('jquery', 'pk-aqp-admin-script'));
    }
    ?>
    <div class="wrap">
        <h2><?= __('AQP Settings', 'air-quality-plugin') ?></h2>

        <form method="post">
            <input type="hidden" name="action" value="save">
            <?php wp_nonce_field('pk_aqp_settings' . $user->ID) ?>
            <?php if($options_values['user_can_set']): ?>
                <h2><?=__('User settings', 'air-quality-plugin')?></h2>
                <?php if(!empty($google_api_key)): ?>
                    <div id="locationField">
                        <input id="autocomplete" type="text">
                        <p class="google-maps-error"></p>
                    </div>
                <?php endif; ?>
                <table class="form-table">
                    <tbody>
                    <tr>
                        <th><?=__('Latitude', 'air-quality-plugin')?></th>
                        <td><input type="text" name="latitude" value="<?=esc_attr($latitude_value)?>"></td>
                    </tr>
                    <tr>
                        <th><?=__('Longitude', 'air-quality-plugin')?></th>
                        <td><input type="text" name="longitude" value="<?=esc_attr($longitude_value)?>"></td>
                    </tr>
                    <tr>
                        <th><?=__('Additional weather info (when available)', 'air-quality-plugin')?></th>
                        <td><input type="checkbox" name="weather-info" <?=checked($weather_info)?>></td>
                    </tr>
                    </tbody>
                </table>
            <?php endif; ?>

            <?php if(current_user_can('manage_options')): ?>
                <h2><?=__('Administrator settings', 'air-quality-plugin')?></h2>
                <?php if(!empty($google_api_key)): ?>
                    <div id="locationField">
                        <input id="autocomplete-admin" type="text">
                        <p class="google-maps-error-admin"></p>
                    </div>
                <?php endif; ?>
                <table class="form-table">
                    <tbody>
                    <tr>
                        <th><?=__('Default latitiude', 'air-quality-plugin')?></th>
                        <td><input type="text" name="latitude-default" value="<?=esc_attr($options_values['latitude'])?>"></td>
                    </tr>
                    <tr>
                        <th><?=__('Default longitude', 'air-quality-plugin')?></th>
                        <td><input type="text" name="longitude-default" value="<?=esc_attr($options_values['longitude'])?>"></td>
                    </tr>
                    <tr>
                        <th><?=__('Google Maps API key', 'air-quality-plugin')?> (<a href="https://developers.google.com/maps/documentation/javascript/get-api-key"><?=__('you can get it here', 'air-quality-plugin')?></a>)</th>
                        <td><input type="text" name="google-maps-key" value="<?=esc_attr($options_values['google_maps_key'])?>"></td>
                    </tr>
                    <tr>
                        <th><?=__('waqi.info API KEY', 'air-quality-plugin')?> (<a href="http://aqicn.org/data-platform/token"><?=__('you can get it here', 'air-quality-plugin')?></a>)</th>
                        <td><input type="text" name="waqi-key" value="<?=esc_attr($options_values['waqi_key'])?>"></td>
                    </tr>
                    <tr>
                        <th><?=__('Additional weather info (when available)', 'air-quality-plugin')?></th>
                        <td><input type="checkbox" name="weather-info-default" <?=checked($options_values['weather_info'])?>></td>
                    </tr>
                    <tr>
                        <th><?=__('Let every user configure', 'air-quality-plugin')?></th>
                        <td><input type="checkbox" name="user-can-set" <?=checked($options_values['user_can_set'])?>></td>
                    </tr>
                    </tbody>
                </table>
            <?php endif;?>
            <input type="submit" name="Submit" class="button-primary" value="<?=__('Save', 'air-quality-plugin')?>">
        </form>
    </div>
    <?php
}
?>