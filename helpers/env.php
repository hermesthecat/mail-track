<?php

/**
 * Environment Helper Functions
 * @author A. Kerem Gök
 */

if (!function_exists('loadEnv')) {
    /**
     * .env dosyasını okur ve değişkenleri $_ENV'e yükler
     * @return void
     */
    function loadEnv()
    {
        $envFile = __DIR__ . '/../.env';
        if (!file_exists($envFile)) {
            die(".env dosyası bulunamadı!");
        }

        $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            if (strpos($line, '#') === 0 || empty($line)) continue;
            list($key, $value) = explode('=', $line, 2);
            $_ENV[trim($key)] = trim($value);
        }
    }
}

if (!function_exists('env')) {
    /**
     * .env dosyasından değer okur
     * @param string $key Anahtar
     * @param mixed $default Varsayılan değer
     * @return mixed
     */
    function env($key, $default = null)
    {
        return $_ENV[$key] ?? $default;
    }
}

// .env dosyasını otomatik yükle
loadEnv();
