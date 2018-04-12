function increase_brightness(hex, percent){
    // strip the leading # if it's there
    hex = hex.replace(/^\s*#|\s*$/g, '');

    // convert 3 char codes --> 6, e.g. `E0F` --> `EE00FF`
    if(hex.length == 3){
        hex = hex.replace(/(.)/g, '$1$1');
    }

    var r = parseInt(hex.substr(0, 2), 16),
        g = parseInt(hex.substr(2, 2), 16),
        b = parseInt(hex.substr(4, 2), 16);

    return '#' +
        ((0|(1<<8) + r + (256 - r) * percent / 100).toString(16)).substr(1) +
        ((0|(1<<8) + g + (256 - g) * percent / 100).toString(16)).substr(1) +
        ((0|(1<<8) + b + (256 - b) * percent / 100).toString(16)).substr(1);
}

jQuery.fn.mouseIsOver = function () {
    return jQuery(this[0]).is(":hover");
};

//Piece of code from jQuery UI for adding one of easing animations
jQuery.extend(jQuery.easing,
    {
        easeOutElastic: function (x, t, b, c, d) {
            var s=1.70158;var p=0;var a=c;
            if (t==0) return b;  if ((t/=d)==1) return b+c;  if (!p) p=d*.3;
            if (a < Math.abs(c)) { a=c; var s=p/4; }
            else var s = p/(2*Math.PI) * Math.asin (c/a);
            return a*Math.pow(2,-10*t) * Math.sin( (t*d-s)*(2*Math.PI)/p ) + c + b;
        }
    }
);

jQuery.fn.animateRotate = function(angle, duration, easing, complete) {
    var args = jQuery.speed(duration, easing, complete);
    var step = args.step;
    return this.each(function(i, e) {
        args.complete = jQuery.proxy(args.complete, e);
        args.step = function(now) {
            jQuery.style(e, 'transform', 'rotate(' + now + 'deg)');
            if (step) return step.apply(e, arguments);
        };

        jQuery({deg: 0}).animate({deg: angle}, args);
    });
};

function initAutocomplete() {
    autocomplete = new google.maps.places.Autocomplete(document.getElementById('autocomplete'));
    autocomplete.addListener('place_changed', getCoords);
}

function getCoords() {
    var errorText = jQuery('.google-maps-error');

    var lattitudeInput = jQuery('#latitude-input');
    var longitudeInput = jQuery('#longitude-input');

    lattitudeInput.attr('disabled', 'disabled');
    longitudeInput.attr('disabled', 'disabled');

    lattitudeInput.val('Proszę czekać...');
    longitudeInput.val('Proszę czekać...');

    var geocoder = new google.maps.Geocoder();
    var address = document.getElementById('autocomplete').value;

    geocoder.geocode({ 'address': address }, function (results, status) {

        if (status == google.maps.GeocoderStatus.OK) {
            lattitudeInput.removeAttr('disabled');
            longitudeInput.removeAttr('disabled');
            var latitude = results[0].geometry.location.lat();
            var longitude = results[0].geometry.location.lng();
            lattitudeInput.val(latitude);
            longitudeInput.val(longitude);
            errorText.html('');
        }
        else {
            lattitudeInput.removeAttr('disabled');
            longitudeInput.removeAttr('disabled');
            lattitudeInput.val('');
            longitudeInput.val('');
            errorText.html(pk_aqp_script_localization.google_api_error);
        }
    });
}

function checked(attr) {
    if(attr == true) {
        return 'checked = "checked"';
    }
    else {
        return '';
    }
}

var pollutionsBoxBgColor = '';

function loadWidget() {
    var sensorPlaceBox = jQuery('.sensor-place');

    var aqiLevelText = jQuery('.aqi-level p');
    var aqiLevelBox = jQuery('.aqi-level');
    var pollutionsBox = jQuery('.pollutions');

    var aqi = aqiLevelText.attr('data-aqi');

    var aqiLevelTextAlign = aqiLevelText.css('text-align');
    var aqiLevelFontSize = aqiLevelText.css('font-size');

    var compassPointer = jQuery('.compass-pointer');
    var windDirection = compassPointer.attr('data-direction');
    compassPointer.animateRotate(windDirection, 2000, 'easeOutElastic');

    aqiLevelBox.css('min-height', aqiLevelBox.outerHeight());

    var message = '';
    if(aqi <= 50) {
        message = pk_aqp_script_localization.alert_level_1;
        boxColor = '#91cb8f';
    }
    if(aqi > 50 && aqi <= 100) {
        message = pk_aqp_script_localization.alert_level_2;
        boxColor = '#c9cb68';
    }
    if(aqi > 100 && aqi <= 150) {
        message = pk_aqp_script_localization.alert_level_3;
        boxColor = '#cb8d53';
    }
    if(aqi > 150 && aqi <= 200) {
        message = pk_aqp_script_localization.alert_level_4;
        boxColor = '#cb413b';
    }
    if(aqi > 200 && aqi <= 300) {
        message = pk_aqp_script_localization.alert_level_5;
        boxColor = '#8f39cb';
    }
    if(aqi > 300) {
        message = pk_aqp_script_localization.alert_level_6;
        boxColor = '#510610'
    }

    pollutionsBoxBgColor = increase_brightness(boxColor, 40);
    var borderBottomSensorPlaceColor = increase_brightness(boxColor, 60);

    pollutionsBox.css('background-color', pollutionsBoxBgColor);
    aqiLevelBox.css("background-color", boxColor);

    sensorPlaceBox.css({
        'background-color': boxColor,
        'border-bottom': '2px dashed ' + borderBottomSensorPlaceColor
    });

    function aqiLevelBoxHoverIn() {
        aqiLevelText.fadeOut(100, function() {
            aqiLevelText.css({
                "text-align": "left",
                "font-size": "12px",
                "line-height": "15px"
            });
            aqiLevelText.html(message);
        });
        aqiLevelText.fadeIn(100, function() {
            if(!aqiLevelBox.mouseIsOver()) {
                aqiLevelBoxHoverOut();
            }
            else {
                aqiLevelText.clearQueue();
            }
        });
    }
    function aqiLevelBoxHoverOut() {
        aqiLevelText.fadeOut(100, function() {
            aqiLevelText.css({
                "text-align": aqiLevelTextAlign,
                "font-size": aqiLevelFontSize,
                "line-height": aqiLevelFontSize
            });
            aqiLevelText.html(aqi);
        });
        aqiLevelText.fadeIn(100, function() {
            if(aqiLevelBox.mouseIsOver()) {
                aqiLevelBoxHoverIn();
            }
            else {
                aqiLevelText.clearQueue();
            }
        });
    }
    aqiLevelBox.hover(aqiLevelBoxHoverIn, aqiLevelBoxHoverOut);
}

jQuery(document).ready(function() {

    loadWidget();

    var widgetData = jQuery('.pk_aqp_widget_data');
    var googleApiIncluded = false;
    var cityChanged = false;
    var widgetHtmlTemp = '';
    jQuery(document).on('click', '.open-settings', function($) {
        jQuery('.open-settings').animateRotate(480, 6000, 'linear');
        widgetHtmlTemp = jQuery('.pk_aqp_widget_data').html();
        var data = {
            action: 'pk_aqp_load_settings_fields'
        };
        jQuery.get(pk_aqp_variables.ajax_url, data, function(recv) {
            var html =
                '<p class="top-paragraph">'+pk_aqp_script_localization.settings+'<img id="back-settings" src="'+ pk_aqp_variables.plugin_url +'img/back.png"></p>' +
                '<div class="settings-section"> ' +
                '<p>'+pk_aqp_script_localization.localization+'</p>' +
                '<input type="text" class="settings-input" id="autocomplete"> ' +
                '<input type="hidden" id="ajax-nonce" value="'+ recv['ajax_nonce'] +'"> ' +
                '<p>'+pk_aqp_script_localization.longitude+'</p> ' +
                '<input type="text" class="settings-input" id="longitude-input" value="'+ recv['longitude'] +'">' +
                '<p>'+pk_aqp_script_localization.latitude+'</p> ' +
                '<input type="text" class="settings-input" id="latitude-input" value="'+ recv['latitude'] +'">' +
                '<p>'+pk_aqp_script_localization.weather+'</p>' +
                '<input type="checkbox" id="weather-input"' + checked(recv['weather']) + '>' +
                '<p class="settings-error"></p>' +
                '<button id="save-button" class="settings-button">'+pk_aqp_script_localization.save+'</button> ' +
                '</div>';
            widgetData.html(html);

            jQuery('.settings-section').css({
                'background-color': boxColor,
                'border-bottom': '3px dashed ' + pollutionsBoxBgColor,
                'border-top': '3px dashed ' + pollutionsBoxBgColor
            });
            jQuery('#save-button').css({
               'background-color': pollutionsBoxBgColor
            });
            jQuery('.settings-input').css({
                'background-color': pollutionsBoxBgColor
            });
            if(!googleApiIncluded) {
                googleApiIncluded = true;
                jQuery.getScript("https://maps.googleapis.com/maps/api/js?key=" + recv['google_maps_key'] + "&libraries=places&callback=initAutocomplete", function (data, textStatus, jqxhr) {
                    console.log(data); //data returned
                    console.log(textStatus); //success
                    console.log(jqxhr.status); //200
                    console.log('Load was performed.');
                });
            }
            else {
                initAutocomplete();
            }
        });
    });
    jQuery(document).on('click', '#back-settings', function() {
        widgetData.html(widgetHtmlTemp);
        loadWidget();
    });

    jQuery(document).on('click', '#save-button', function() {
        var settingsError = jQuery('.settings-error');
        var saveButton = jQuery('#save-button');
        saveButton.html('');
        saveButton.css({
            'background-image': 'url(' + pk_aqp_variables.plugin_url + 'img/ajax-loader.gif)',
            'background-repeat': 'no-repeat',
            'background-position': 'center',
            'background-size': '30px 30px'
        });
        data = {
            action: 'pk_aqp_save_user_coordinates',
            'ajax_nonce': jQuery('#ajax-nonce').val(),
            'longitude': jQuery('#longitude-input').val(),
            'latitude': jQuery('#latitude-input').val(),
            'weather': jQuery('#weather-input').is(':checked')
        };
        jQuery.get(pk_aqp_variables.ajax_url, data, function(recv) {
            if(recv['message'] === '') {
                data = {
                    action: 'pk_aqp_get_widget_html'
                };
                jQuery.get(pk_aqp_variables.ajax_url, data, function(recv) {
                    widgetData.html(recv);
                    loadWidget();
                });
            }
            else {
                settingsError.html(recv['message']);
                settingsError.css({
                    'display': 'block'
                });
                saveButton.html('Save');
                saveButton.css({
                    'background-image': 'none'
                })
            }
        });
    })
});