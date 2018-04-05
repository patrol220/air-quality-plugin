function initAutocomplete() {
    autocomplete = new google.maps.places.Autocomplete(document.getElementById('autocomplete'));
    autocomplete.addListener('place_changed', getCoords);
    autocompleteAdmin = new google.maps.places.Autocomplete(document.getElementById('autocomplete-admin'));
    autocompleteAdmin.addListener('place_changed', getCoordsAdmin);
}

function getCoords() {
    var errorText = jQuery('.google-maps-error');

    var lattitudeInput = jQuery('[name=latitude]');
    var longitudeInput = jQuery('[name=longitude]');

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
            errorText.html(pk_aqp_admin_l10n.google_api_error);
        }
    });
}

function getCoordsAdmin() {
    var errorTextAdmin = jQuery('.google-maps-error-admin');

    var lattitudeInput = jQuery('[name=latitude-default]');
    var longitudeInput = jQuery('[name=longitude-default]');

    lattitudeInput.attr('disabled', 'disabled');
    longitudeInput.attr('disabled', 'disabled');

    lattitudeInput.val('Proszę czekać...');
    longitudeInput.val('Proszę czekać...');

    var geocoder = new google.maps.Geocoder();
    var address = document.getElementById('autocomplete-admin').value;

    geocoder.geocode({ 'address': address }, function (results, status) {

        if (status == google.maps.GeocoderStatus.OK) {
            lattitudeInput.removeAttr('disabled');
            longitudeInput.removeAttr('disabled');
            var latitude = results[0].geometry.location.lat();
            var longitude = results[0].geometry.location.lng();
            lattitudeInput.val(latitude);
            longitudeInput.val(longitude);
            errorTextAdmin.html('');
        }
        else {
            lattitudeInput.removeAttr('disabled');
            longitudeInput.removeAttr('disabled');
            lattitudeInput.val('');
            longitudeInput.val('');
            errorTextAdmin.html(pk_aqp_admin_l10n.google_api_error)
        }
    });
}

function gm_authFailure() {
    jQuery('.google-maps-error').html(pk_aqp_admin_l10n.google_api_authentication_error);
    jQuery('.google-maps-error-admin').html(pk_aqp_admin_l10n.google_api_authentication_error);
}

//Disable submiting form by pressing enter key on google fields
jQuery(document).on('keyup keypress', 'form #autocomplete-admin, form #autocomplete', function(e) {
    if(e.keyCode === 13) {
        e.preventDefault();
        return false;
    }
});