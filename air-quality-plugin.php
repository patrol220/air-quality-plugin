<?php

/*
 * Plugin Name: Air Quality Plugin
 * Description: Plugin for displaying air quality data
 * Version: 0.40
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

define( 'PK_AQP_DIR_PATH', plugin_dir_url( __FILE__ ) );
define('PK_AQP_VERSION', 0.4);

include 'includes/widget_display.php';
include 'includes/sanitization_validation_functions.php';
include 'includes/options_menu.php';
include 'includes/handle_ajax.php';

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

    //function pk_aqp_options_code() can be found in includes/options_menu.php
    add_options_page(__('AQP Settings', 'air-quality-plugin'), __('AQP Settings', 'air-quality-plugin'), $capability, __FILE__, 'pk_aqp_options_code');
}
add_action('admin_menu', 'pk_aqp_create_options_page');

//Widget initialization

function pk_aqp_widget_init() {
    register_widget('pk_aqp_air_quality_widget');
}
add_action('widgets_init', 'pk_aqp_widget_init');

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

        wp_enqueue_style('pk-aqp-widget-style', plugin_dir_url(__FILE__) . 'css/style-widget.css', array(), PK_AQP_VERSION);
        pk_aqp_show_widget();

        echo $after_widget;
    }
}

?>