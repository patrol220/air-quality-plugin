<?php


function pk_aqp_display_air_quality_data($data_json)
{
    $script_localization_array = array(
        'alert_level_1' => __('Air quality is considered satisfactory, and air pollution poses little or no risk.', 'air-quality-plugin'),
        'alert_level_2' => __('Air quality is acceptable; however, for some pollutants there may be a moderate health concern for a very small number of people who are unusually sensitive to air pollution.', 'air-quality-plugin'),
        'alert_level_3' => __('Members of sensitive groups may experience health effects. The general public is not likely to be affected.', 'air-quality-plugin'),
        'alert_level_4' => __('Everyone may begin to experience health effects; members of sensitive groups may experience more serious health effects.', 'air-quality-plugin'),
        'alert_level_5' => __('Health alert: everyone may experience more serious health effects.', 'air-quality-plugin'),
        'alert_level_6' => __('Health warnings of emergency conditions. The entire population is more likely to be affected.', 'air-quality-plugin'),
        'google_api_error' => __('Something went wrong during retrieving information from google, try again', 'air-quality-plugin'),
        'localization' => __('Localization', 'air-quality-plugin'),
        'longitude' => __('Longitude', 'air-quality-plugin'),
        'latitude' => __('Latitude', 'air-quality-plugin'),
        'weather' => __('Weather info', 'air-quality-plugin'),
        'settings' => __('Settings', 'air-quality-plugin'),
        'save' => __('Save', 'air-quality-plugin'),
    );

    wp_enqueue_script('pk-aqp-widget-script', PK_AQP_DIR_PATH . 'js/script-widget.js', array('jquery'), PK_AQP_VERSION);
    wp_localize_script('pk-aqp-widget-script', 'pk_aqp_script_localization', $script_localization_array);

    $protocol = isset( $_SERVER["HTTPS"]) ? 'https://' : 'http://';
    $params = array(
        'ajax_url' => admin_url( 'admin-ajax.php', $protocol ),
        'plugin_url' => PK_AQP_DIR_PATH
    );
    wp_localize_script( 'pk-aqp-widget-script', 'pk_aqp_variables', $params );

    $options = get_option('pk_aqp_options');

    if ($options['user_can_set'] && is_user_logged_in()) {
        $user = wp_get_current_user();
        if (get_user_meta($user->ID, 'weather-info', true)) {
            $display_weather_info = get_user_meta($user->ID, 'weather-info', true);
        } else {
            $display_weather_info = false;
        }
    } else {
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
    <div class="pk_aqp_widget_data">
        <p class="top-paragraph"><?= __('Last update', 'air-quality-plugin') ?>: <?= $last_update ?>
            <?php if($options['user_can_set'] && is_user_logged_in() && current_user_can('read')): ?>
            <img class="open-settings" src="<?= PK_AQP_DIR_PATH . 'img/settings.png' ?>">
            <?php endif;?>
        </p>
        <div class="sensor-place">
            <p><span><b><?= __('Detector place', 'air-quality-plugin') ?>: </b> <?= $place ?></span></p>
        </div>
        <div class="aqi-level">
            <h5><?= __('AQI Level', 'air-quality-plugin') ?></h5>
            <p class="amount" data-aqi="<?= $aqi ?>"><?= $aqi ?></p>
        </div>
        <div class="pollutions">
            <div class="details">
                <?php if ($co): ?>
                    <span><b>CO:</b> <?= $co ?></span>
                <?php endif; ?>
                <?php if ($no2): ?>
                    <span><b>NO<sub>2</sub>:</b> <?= $no2 ?></span>
                <?php endif; ?>
                <?php if ($o3): ?>
                    <span><b>O<sub>3</sub>:</b> <?= $o3 ?></span>
                <?php endif; ?>
                <?php if ($pm10): ?>
                    <span><b>PM10:</b> <?= $pm10 ?></span>
                <?php endif; ?>
                <?php if ($pm25): ?>
                    <span><b>PM2.5:</b> <?= $pm25 ?></span>
                <?php endif; ?>
                <?php if ($so2): ?>
                    <span><b>SO<sub>2</sub>:</b> <?= $so2 ?></span>
                <?php endif; ?>
            </div>
        </div>

        <?php if ($display_weather_info): ?>
            <div class="weather">
                <?php if ($wind): ?>
                    <div class="weather-data">
                        <img src="<?= PK_AQP_DIR_PATH . 'img/wind.png' ?>">
                        <p><?= $wind ?> m/s</p>
                    </div>
                <?php endif; ?>
                <?php if ($wind_direction): ?>
                    <div class="weather-data compass">
                        <img class="compass-body" src="<?= PK_AQP_DIR_PATH . 'img/compass-body.png' ?>">
                        <img data-direction="<?= $wind_direction ?>" class="compass-pointer"
                             src="<?= PK_AQP_DIR_PATH . 'img/compass-pointer.png' ?>">
                        <p><?= $wind_direction ?>°</p>
                    </div>
                <?php endif; ?>
                <?php if ($pressure): ?>
                    <div class="weather-data">
                        <img src="<?= PK_AQP_DIR_PATH . 'img/barometer.png' ?>">
                        <p><?= $pressure ?> hPa</p>
                    </div>
                <?php endif; ?>
                <?php if ($humidity): ?>
                    <div class="weather-data">
                        <img src="<?= PK_AQP_DIR_PATH . 'img/humidity.png' ?>">
                        <p><?= $humidity ?> %</p>
                    </div>
                <?php endif; ?>
                <?php if ($temperature): ?>
                    <div class="weather-data">
                        <img src="<?= PK_AQP_DIR_PATH . 'img/thermometer.png' ?>">
                        <p><?= $temperature ?>°C</p>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
    <?php
}

function pk_aqp_show_widget()
{
    $user = wp_get_current_user();
    $options = get_option('pk_aqp_options');

    if (empty($options['waqi_key'])) {
        if (current_user_can('manage_options')) {
            ?>
            <div class="api-error">
                <p><?= __('Please set api key for waqi.info in settings', 'air-quality-plugin') ?></p></div>
            <?php
        } else {
            ?>
            <div class="api-error">
                <p><?= __('The administrator has not set up plugin properly yet so it is not working', 'air-quality-plugin') ?></p>
            </div>
            <?php
        }
    } else {
        $waqi_key = $options['waqi_key'];

        if (!$options['user_can_set'] || empty(get_user_meta($user->ID, 'latitude', true)) || empty(get_user_meta($user->ID, 'longitude', true))) {
            $latitude = $options['latitude'];
            $longitude = $options['longitude'];
        } else {
            $latitude = get_user_meta($user->ID, 'latitude', true);
            $longitude = get_user_meta($user->ID, 'longitude', true);
        }

        $transient_string = $latitude . ';' . $longitude;
        if (get_transient($transient_string) == false) {

            $connect_tries = 0;
            $url = "http://api.waqi.info/feed/geo:$latitude;$longitude/?token=$waqi_key";
            do {
                if($connect_tries > 0) {
                    sleep(0.5);
                }
                $api_data = wp_remote_get($url);
                $api_data_decoded = json_decode($api_data['body']);
                $connect_tries++;
            } while($api_data_decoded->status == 'nug' && $connect_tries < 5); //Try to download data again if there is kinky 'nug' status

            if (!is_wp_error($api_data)) {
                if ($api_data['response']['code'] == 200) {
                    $response = json_decode($api_data['body']);
                    if ($response->status == 'error') {
                        if ($response->data = 'Invalid key') {
                            ?>
                            <div class="api-error">
                                <p><?= __('Please check your waqi.info api key. It seems that wrong api key was given', 'air-quality-plugin') ?></p>
                            </div>
                            <?php
                        }
                        else {
                            ?>
                            <div class="api-error">
                                <p><?= __('There was an error during connecting to API', 'air-quality-plugin') ?>: <?= $response->data ?></p>
                            </div>
                            <?php
                        }
                    } else {
                        set_transient($transient_string, $api_data['body'], 900);
                        pk_aqp_display_air_quality_data($api_data['body']);
                    }
                } else {
                    ?>
                    <div class="api-error">
                        <p><?= __('There was an error during connecting to API', 'air-quality-plugin') ?></p>
                    </div>
                    <?php
                }
            } else { // if WP_Error occured
                $error = $api_data->get_error_message();
                ?>
                <div class="api-error">
                    <p><?= __('There was an error during connecting to API', 'air-quality-plugin') ?>: <?= $error ?></p>
                </div>
                <?php
            }
        } else {
            $transient_data = get_transient($transient_string);
            pk_aqp_display_air_quality_data($transient_data);
        }
    }
}

?>