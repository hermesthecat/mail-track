<?php

/**
 * Database Connection Helper
 * @author A. Kerem Gök
 */

// Veritabanı bağlantısı
try {
    $pdo = new PDO(
        "mysql:host=" . env('DB_HOST') . ";dbname=" . env('DB_NAME'),
        env('DB_USER'),
        env('DB_PASS')
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Veritabanı bağlantı hatası! Lütfen sistem yöneticisi ile iletişime geçin.");
}
