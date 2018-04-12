<?php

function pk_aqp_longitude_filter($longitude)
{
    if (isset($longitude) && $filtered = filter_var($longitude, FILTER_VALIDATE_FLOAT)) {
        if ($filtered >= -180 && $filtered <= 180) {
            return round($filtered, 2);
        } else {
            return false;
        }
    } else {
        return false;
    }
}

function pk_aqp_latitude_filter($latitude)
{
    if (isset($latitude) && $filtered = filter_var($latitude, FILTER_VALIDATE_FLOAT)) {
        if ($filtered >= -90 && $filtered <= 90) {
            return round($filtered, 2);
        } else {
            return false;
        }
    } else {
        return false;
    }
}

?>