<?php

/**
 * Admin Şifre Sıfırlama
 * @author A. Kerem Gök
 */

require_once __DIR__ . '/helpers/env.php';

try {
    // Veritabanı bağlantısı
    $pdo = new PDO(
        "mysql:host=" . env('DB_HOST') . ";dbname=" . env('DB_NAME'),
        env('DB_USER'),
        env('DB_PASS')
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Yeni admin hesabı için güvenli şifre hash'i oluştur
    $username = 'admin';
    $password = 'admin123';
    $password_hash = password_hash($password, PASSWORD_DEFAULT);
    $role = 'admin';

    // Önce mevcut admin hesabını kontrol et
    $stmt = $pdo->prepare("SELECT id FROM admins WHERE username = ?");
    $stmt->execute([$username]);
    $existing_admin = $stmt->fetch();

    if ($existing_admin) {
        // Mevcut admin hesabını güncelle
        $stmt = $pdo->prepare("UPDATE admins SET password = ?, is_active = 1 WHERE username = ?");
        $stmt->execute([$password_hash, $username]);
        echo "Admin hesabı güncellendi!\n";
    } else {
        // Yeni admin hesabı oluştur
        $stmt = $pdo->prepare("INSERT INTO admins (username, password, role, is_active) VALUES (?, ?, ?, 1)");
        $stmt->execute([$username, $password_hash, $role]);
        echo "Yeni admin hesabı oluşturuldu!\n";
    }

    echo "\nGiriş bilgileri:\n";
    echo "Kullanıcı adı: admin\n";
    echo "Şifre: admin123\n";
} catch (PDOException $e) {
    die("Hata: " . $e->getMessage());
}
