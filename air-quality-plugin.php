<?php

/*
 * Plugin Name: Air Quality Plugin
 * Description: Plugin for displaying air quality data
 * Version: 0.11
 * Author: Patryk Kasiczak
 * License: GPLv2 or later
 */

/*
This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.

Copyright 2005-2015 Automattic, Inc.
*/

?>
<?php

function pk_aqp_longitude_filter($longitude) {
    if(isset($longitude) && $filtered = filter_var($longitude, FILTER_VALIDATE_FLOAT)) {
        if($filtered >= -180 && $filtered <= 180) {
            return round($filtered, 6);
        }
        else {
            return false;
        }
    }
    else {
        return false;
    }
}

function pk_aqp_latitude_filter($latitude)
{
    if (isset($latitude) && $filtered = filter_var($latitude, FILTER_VALIDATE_FLOAT)) {
        if ($filtered >= 0 && $filtered <= 90) {
            return round($filtered, 6);
        }
        else {
            return false;
        }
    }
    else {
        return false;
    }
}

function pk_aqp_plugin_activated() {
    $options_default = array(
        'latitude' => '52.232600',
        'longitude' => '20.78101',
        'user_can_set' => false,
        'weather_info' => true
    );
    add_option('pk_aqp_options', $options_default);
}
register_activation_hook(__FILE__, 'pk_aqp_plugin_activated');

function pk_aqp_plugin_uninstall() {
    delete_option('pk_aqp_options');

    //delete users plugin metadata
    $users = get_users();
    foreach($users as $user) {
        delete_user_meta($user->ID, 'longitude');
        delete_user_meta($user->ID, 'latitude');
        delete_user_meta($user->ID, 'weather-info');
    }
}
register_uninstall_hook(__FILE__, 'pk_aqp_plugin_uninstall');

function pk_aqp_prepare_translations() {
    load_plugin_textdomain('air-quality-plugin', false, basename( dirname( __FILE__ ) ) . '/languages');
}
add_action('plugins_loaded', 'pk_aqp_prepare_translations');

//Adding options page
function pk_aqp_create_options_page() {
    $options = get_option('pk_aqp_options');
    $capability = $options['user_can_set'] ? 'read' : 'manage_options';
    add_options_page(__('AQP Settings', 'air-quality-plugin'), __('AQP Settings', 'air-quality-plugin'), $capability, __FILE__, 'pk_aqp_options_code');
}
add_action('admin_menu', 'pk_aqp_create_options_page');

//Options code
function pk_aqp_options_code() {
    $user = wp_get_current_user();

    if($_POST['action'] == 'save') {
        check_admin_referer('pk_aqp_settings' . $user->ID); //nonce

        if(current_user_can('read')) {
            $weather_info = isset($_POST['weather-info']) ? true : false;
            update_user_meta($user->ID, 'weather-info', $weather_info);

            if (pk_aqp_longitude_filter($_POST['longitude']) && pk_aqp_latitude_filter($_POST['latitude'])) {
                $longitude = pk_aqp_longitude_filter($_POST['longitude']);
                $latitude = pk_aqp_latitude_filter($_POST['latitude']);
                update_user_meta($user->ID, 'longitude', $longitude);
                update_user_meta($user->ID, 'latitude', $latitude);
            }

        }

        if(current_user_can('manage_options')) {
            if (pk_aqp_longitude_filter($_POST['longitude-default']) && pk_aqp_latitude_filter($_POST['latitude-default'])) {
                $longitude_default = pk_aqp_longitude_filter($_POST['longitude-default']);
                $latitude_default = pk_aqp_latitude_filter($_POST['latitude-default']);
            }

            $google_maps_key = $_POST['google-maps-key'];
            $user_can_set = isset($_POST['user-can-set']) ? true : false;
            $weather_info_default = isset($_POST['weather-info-default']) ? true : false;
            $options = array(
                'longitude' => $longitude_default,
                'latitude' => $latitude_default,
                'user_can_set' => $user_can_set,
                'weather_info' => $weather_info_default,
                'google_maps_key' => $google_maps_key
            );
            update_option('pk_aqp_options', $options);
        }
    }

    $options_values = get_option('pk_aqp_options');

    $longitude_value = get_user_meta($user->ID, 'longitude', true);
    $latitude_value = get_user_meta($user->ID, 'latitude', true);
    $weather_info = get_user_meta($user->ID, 'weather-info', true);

    $google_api_key = $options_values['google_maps_key'];

    $admin_script_localization = array(
            'google_api_error' => __('Something went wrong during retrieving information from google, try again', 'air-quality-plugin')
    );

    wp_enqueue_style('pk-aqp-admin-style', plugin_dir_url(__FILE__) . 'css/style-admin.css');
    wp_enqueue_script('pk-aqp-admin-script', plugin_dir_url(__FILE__) . 'js/script-admin.js', array('jquery'));
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
                        <th><?=__('Google Maps API key', 'air-quality-plugin')?></th>
                        <td><input type="text" name="google-maps-key" value="<?=esc_attr($options_values['google_maps_key'])?>"></td>
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
            <input type="submit" name="Submit" class="button-primary" value="Zapisz">
        </form>
    </div>
    <?php
}

//Widget initialization
add_action('widgets_init', 'pk_aqp_widget_init');
function pk_aqp_widget_init() {
    register_widget('pk_aqp_air_quality_widget');
}

function pk_aqp_display_air_quality_data($data_json) {

    $script_localization_array = array(
        'alert_level_1' => __('Air quality is considered satisfactory, and air pollution poses little or no risk.', 'air-quality-plugin'),
        'alert_level_2' => __('Air quality is acceptable; however, for some pollutants there may be a moderate health concern for a very small number of people who are unusually sensitive to air pollution.', 'air-quality-plugin'),
        'alert_level_3' => __('Members of sensitive groups may experience health effects. The general public is not likely to be affected.', 'air-quality-plugin'),
        'alert_level_4' => __('Everyone may begin to experience health effects; members of sensitive groups may experience more serious health effects.', 'air-quality-plugin'),
        'alert_level_5' => __('Health alert: everyone may experience more serious health effects.', 'air-quality-plugin'),
        'alert_level_6' => __('Health warnings of emergency conditions. The entire population is more likely to be affected.', 'air-quality-plugin'),
    );

    wp_enqueue_script('pk-aqp-widget-script', plugin_dir_url(__FILE__) . 'js/script-widget.js', array('jquery'), '0.1');
    wp_localize_script('pk-aqp-widget-script', 'pk_aqp_script_localization', $script_localization_array);
    wp_enqueue_style('pk-aqp-widget-style', plugin_dir_url(__FILE__) . 'css/style-widget.css', array(), '0.1');

    $options = get_option('pk_aqp_options');

    if($options['user_can_set'] && is_user_logged_in()) {
        $user = wp_get_current_user();
        if (get_user_meta($user->ID, 'weather-info', true)) {
            $display_weather_info = get_user_meta($user->ID, 'weather-info', true);
        } else {
            $display_weather_info = false;
        }
    }
    else {
        $display_weather_info = $options['weather_info'];
    }
    
    $aq_data = json_decode($data_json);

    $last_update = $aq_data->data->time->s;
    $last_update = date('d-m-y H:i', strtotime($last_update));

    $time_zone = $aq_data->data->time->tz;

    $place = $aq_data->data->city->name;
    $aqi = $aq_data->data->aqi;

    $iaqi = $aq_data->data->iaqi;
    $co = isset($iaqi->co->v) ? $iaqi->co->v : false;
    $humidity = isset($iaqi->h->v) ? round($iaqi->h->v) : false;
    $no2 = isset($iaqi->no2->v) ? $iaqi->no2->v : false;
    $o3 = isset($iaqi->o3->v) ? $iaqi->o3->v : false;
    $pressure = isset($iaqi->p->v) ? round($iaqi->p->v) : false;
    $pm10 = isset($iaqi->pm10->v) ? $iaqi->pm10->v : false;
    $pm25 = isset($iaqi->pm25->v) ? $iaqi->pm25->v : false;
    $so2 = isset($iaqi->so2->v) ? $iaqi->so2->v : false;
    $temperature = isset($iaqi->t->v) ? round($iaqi->t->v, 2) : false;
    $wind = isset($iaqi->w->v) ? round($iaqi->w->v, 2) : false;
    $wind_direction = isset($iaqi->wd->v) ? round($iaqi->wd->v) : false;
    ?>
    <p class="top-paragraph"><?=__('Last update', 'air-quality-plugin')?>: <?=$last_update?></p>
    <div class="sensor-place">
        <p><span><b><?=__('Detector place', 'air-quality-plugin')?>: </b> <?= $place ?></span></p>
    </div>
    <div class="aqi-level">
        <h5><?=__('AQI Level', 'air-quality-plugin')?></h5>
        <p class="amount" data-aqi="<?=$aqi?>"><?=$aqi?></p>
    </div>
    <div class="pollutions">
        <div class="details">
            <?php if($co): ?>
            <span><b>CO:</b> <?=$co?></span>
            <?php endif; ?>
            <?php if($no2): ?>
            <span><b>NO<sub>2</sub>:</b> <?=$no2?></span>
            <?php endif; ?>
            <?php if($o3): ?>
            <span><b>O<sub>3</sub>:</b> <?=$o3?></span>
            <?php endif; ?>
            <?php if($pm10): ?>
            <span><b>PM10:</b> <?=$pm10?></span>
            <?php endif; ?>
            <?php if($pm25): ?>
            <span><b>PM2.5:</b> <?=$pm25?></span>
            <?php endif; ?>
            <?php if($so2): ?>
            <span><b>SO<sub>2</sub>:</b> <?=$so2?></span>
            <?php endif; ?>
        </div>
    </div>

    <?php if($display_weather_info): ?>
    <div class="weather">
        <?php if($wind): ?>
        <div class="weather-data">
            <img src="<?=plugin_dir_url(__FILE__) . 'img/wind.png'?>">
            <p><?=$wind?> m/s</p>
        </div>
        <?php endif; ?>
        <?php if($wind_direction): ?>
        <div class="weather-data compass">
            <img class="compass-body" src="<?=plugin_dir_url(__FILE__) . 'img/compass-body.png'?>">
            <img data-direction="<?=$wind_direction?>" class="compass-pointer" src="<?=plugin_dir_url(__FILE__) . 'img/compass-pointer.png'?>">
            <p><?=$wind_direction?>°</p>
        </div>
        <?php endif; ?>
        <?php if($pressure): ?>
        <div class="weather-data">
            <img src="<?=plugin_dir_url(__FILE__) . 'img/barometer.png'?>">
            <p><?=$pressure?> hPa</p>
        </div>
        <?php endif; ?>
        <?php if($humidity): ?>
        <div class="weather-data">
            <img src="<?=plugin_dir_url(__FILE__) . 'img/humidity.png'?>">
            <p><?=$humidity?> %</p>
        </div>
        <?php endif; ?>
        <?php if($temperature): ?>
        <div class="weather-data">
            <img src="<?=plugin_dir_url(__FILE__) . 'img/thermometer.png'?>">
            <p><?=$temperature?>°C</p>
        </div>
        <?php endif; ?>
    </div>
    <?php endif;?>
    <?php

}

//Widget class
class pk_aqp_air_quality_widget extends WP_Widget {
    function __construct() {
        $widget_ops = array(
            'classname' => 'pk_aqp_air_quality_widget',
            'description' => __('Displays info about air quality', 'air-quality-plugin')
        );
        $this->WP_Widget('pk_aqp_air_quality_widget', 'Air Quality Widget', $widget_ops);
    }

    function form($instance) {
        $defaults = array(
            'title' => 'Air Quality Widget'
        );
        $instance = wp_parse_args((array)$instance, $defaults);
        $title = $instance['title'];
        ?>

        <p><?=__('Title', 'air-quality-plugin')?>: <input type="text" class="widefat" name="<?=$this->get_field_name('title')?>" value="<?=esc_attr($title)?>"></p>

        <?php
    }

    function update($new_instance, $old_instance)
    {
        $instance = $old_instance;
        $instance['title'] = strip_tags($new_instance['title']);
        return $instance;
    }

    function widget($args, $instance)
    {
        extract($args);
        echo $before_widget;
        $title = apply_filters('widget_title', $instance['title']);
        if(!empty($title)) {
            echo $before_title . $title . $after_title;
        }

        $user = wp_get_current_user();

        $token = '304bab739cd27a0c110e34b2b1a598d095e4eaec';

        $options = get_option('pk_aqp_options');

        if(!$options['user_can_set'] || empty(get_user_meta($user->ID, 'latitude', true)) || empty(get_user_meta($user->ID, 'longitude', true))) {
            $latitude = $options['latitude'];
            $longitude = $options['longitude'];
        }
        else {
            $latitude = get_user_meta($user->ID, 'latitude', true);
            $longitude = get_user_meta($user->ID, 'longitude', true);
        }

        $transient_string = $latitude . ';' . $longitude;

        if(get_transient($transient_string) == false) {

            $url = "http://api.waqi.info/feed/geo:$latitude;$longitude/?token=$token";
            $api_data = wp_remote_get($url);
            if (!is_wp_error($api_data)) {
                if ($api_data['response']['code'] == 200) {
                    set_transient($transient_string, $api_data['body'], 900);
                    pk_aqp_display_air_quality_data($api_data['body']);
                } else {
                    var_dump($api_data);
                }
            } else { // if WP_Error occured
                $error = $api_data->get_error_message();
                ?>

                <p><?=__('There was an error during connction to API', 'air-quality-plugin')?>: <?= $error ?></p>

                <?php
            }
        }
        else {
            $transient_data = get_transient($transient_string);
            pk_aqp_display_air_quality_data($transient_data);
        }

        echo $after_widget;
    }
}

?>