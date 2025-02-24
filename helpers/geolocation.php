<?php

/**
 * Geolocation Helper Functions
 * @author A. Kerem Gök
 */

// IP2Location veya MaxMind GeoIP için API anahtarı
define('GEOIP_API_KEY', env('GEOIP_API_KEY'));

// Yardımcı fonksiyonlar
function getGeoLocation($ip)
{
    // IP2Location veya başka bir servis kullanarak konum bilgisi alınabilir
    $url = "http://api.ipapi.com/" . $ip . "?access_key=" . GEOIP_API_KEY;
    $response = @file_get_contents($url);
    return json_decode($response, true);
}
