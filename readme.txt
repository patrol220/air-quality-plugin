=== Air Quality Plugin ===
Contributors: patrol220
Donate link: paypal.me/patrol220
Tags: weather, pollution, monitor, widget, health
Requires at least: 4.7
Tested up to: 4.9.4
Requires PHP: 5.5
Stable tag: 0.3
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

This plugin was made mainly to display air quality from closest air pollution detector

== Description ==

Air Quality Plugin shows air quality data from closest detector of localization which you will give in settings of plugin. In plugin settings localization must be provided to determine which detector will be chosen. You can set Google Maps API key in settings to make that thing easier. After giving Google Maps Api key you will get new input field where you can specify your localization. It can be name of the city, but in your city there can be multiple air quality detectors so for more accurate results you should put name of the street.
Plugin is using waqi.info JSON API to get data about air quality.

In settings administrator have option to let every user to set localization from Settings -> AQP Settings what they want.

Plugin additionaly displays some info about weather from detector if there is any given. You can disable it from administrator options.

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/air-quality-plugin` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress
3. Put Air Quality Widget where you want it to be
4. Use the Settings->AQP Settings screen to configure the plugin

== Screenshots ==

1. screenshot-1.png
2. screenshot-2.png
3. screenshot-3.png
4. screenshot-4.png
5. screenshot-5.png
6. screenshot-6.png

== Changelog ==

= 0.3 =
* Added field in settings for providing waqi.info API key
* Some improvements in ux

= 0.2 =
* API connection error message
* Upgrade in translation

= 0.11 =
* Upgrades in responsivity
* Prepared for translating

= 0.1 =
* First version of plugin

== Upgrade Notice ==

= 0.3 =
There were added field in settings for providing waqi.info API key so now you must generate your own one, there was also changes in informing users and administrator about errors

= 0.2 =
Added more elegant way to display error when connecting with API went wrong

= 0.11 =
There were changes in css and html to improve displaying in mobile devices. Also there were included anything what you need to translate plugin.