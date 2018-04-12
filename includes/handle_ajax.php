<?php
function pk_aqp_load_settings_fields() {
    $response = array();
    $user = wp_get_current_user();
    $options = get_option('pk_aqp_options');
    $response['google_maps_key'] = $options['google_maps_key'];
    $response['longitude'] = get_user_meta($user->ID, 'longitude', true);
    $response['latitude'] = get_user_meta($user->ID, 'latitude', true);
    $response['weather'] = get_user_meta($user->ID, 'weather-info', true);
    $response['ajax_nonce'] = wp_create_nonce('save-ajax-settings' . $user->ID);
    wp_send_json($response);
}
add_action('wp_ajax_pk_aqp_load_settings_fields', 'pk_aqp_load_settings_fields');

function pk_aqp_save_user_coordinates() {
    $user = wp_get_current_user();
    check_ajax_referer('save-ajax-settings' . $user->ID, 'ajax_nonce');
    $response = array();
    $response['message'] = '';
    $user = wp_get_current_user();
    $latitude = pk_aqp_latitude_filter($_GET['latitude']);
    $longitude = pk_aqp_longitude_filter($_GET['longitude']);
    $weather = $_GET['weather'] == 'true' ? true : false;

    if($latitude && $longitude) {
        update_user_meta($user->ID, 'latitude', $latitude);
        update_user_meta($user->ID, 'longitude', $longitude);
    }
    else {
        $response['message'] .= __('Given coordinates are wrong. Please give values in range from -180 to 180 for longitude and from -90 to 90 for latitude', 'air-quality-plugin') . ' ';
    }
    update_user_meta($user->ID, 'weather-info', $weather);

    wp_send_json($response);
}
add_action('wp_ajax_pk_aqp_save_user_coordinates', 'pk_aqp_save_user_coordinates');

function pk_aqp_get_widget_html() {
    pk_aqp_show_widget();
    exit();
}
add_action('wp_ajax_pk_aqp_get_widget_html', 'pk_aqp_get_widget_html');
?>