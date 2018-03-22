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

jQuery(document).ready(function() {

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
    
    pollutionsBox.css('min-height', pollutionsBox.outerHeight());
    pollutionsText = jQuery('.pollutions .details');
    pollutionsTextDef = pollutionsText.html();

    var pollutionsTextAlign = pollutionsBox.css('text-align');
    var pollutionsFontSize = pollutionsBox.css('font-size');

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

    var pollutionsBoxBgColor = increase_brightness(boxColor, 40);
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
    /*
    function pollutionsBoxHoverIn() {
        pollutionsText.fadeOut(100, function() {
            pollutionsText.css({
                "text-align": "left",
                "font-size": "12px",
                "line-height": "15px"
            });
            pollutionsText.html(jQuery('.sensor-place').html());
        });
        pollutionsText.fadeIn(100, function() {
            if(!pollutionsBox.mouseIsOver()) {
                pollutionsBoxHoverOut();
            }
            else {
                pollutionsText.clearQueue();
            }
        });
    }
    function pollutionsBoxHoverOut() {
        pollutionsText.fadeOut(100, function() {
            pollutionsText.css({
                "text-align": pollutionsTextAlign,
                "font-size": pollutionsFontSize
            });
            pollutionsText.html(pollutionsTextDef);
        });
        pollutionsText.fadeIn(100, function () {
            if(pollutionsBox.mouseIsOver()) {
                pollutionsBoxHoverIn();
            }
            else {
                pollutionsText.clearQueue();
            }
        });
    }
    pollutionsBox.hover(pollutionsBoxHoverIn, pollutionsBoxHoverOut);
    */
});