<?php

/**
 * Login Page
 * @author A. Kerem Gök
 */

session_start();

// Environment yardımcısını yükle
require_once __DIR__ . '/helpers/env.php';

// Eğer kullanıcı zaten giriş yapmışsa ana sayfaya yönlendir
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

// Veritabanı bağlantısı
try {
    $pdo = new PDO(
        "mysql:host=" . env('DB_HOST') . ";dbname=" . env('DB_NAME'),
        env('DB_USER'),
        env('DB_PASS')
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    error_log("Database connection error: " . $e->getMessage());
    die("Veritabanı bağlantı hatası! Lütfen sistem yöneticisi ile iletişime geçin.");
}

// Login işlemi
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    if (!isset($pdo) || !($pdo instanceof PDO)) {
        $login_error = "Veritabanı bağlantısı kurulamadı!";
    } else {
        try {
            $stmt = $pdo->prepare("SELECT * FROM admins WHERE username = ? AND is_active = 1");
            $stmt->execute([$username]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];

                // Son giriş zamanını güncelle
                $pdo->prepare("UPDATE admins SET last_login = NOW() WHERE id = ?")->execute([$user['id']]);

                // Başarılı girişte ana sayfaya yönlendir
                header('Location: index.php');
                exit;
            } else {
                error_log("Login failed for user: " . $username);
                $login_error = "Geçersiz kullanıcı adı veya şifre!";
            }
        } catch (PDOException $e) {
            error_log("Login error: " . $e->getMessage());
            $login_error = "Giriş yapılırken bir hata oluştu!";
        }
    }
}

?>
<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mail Tracker - Giriş</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="login.css">
</head>

<body>
    <div class="login-card">
        <div class="login-header">
            <i class="bi bi-envelope-check"></i>
            <h2 class="mt-3">Mail Tracker</h2>
            <p class="text-muted">Lütfen giriş yapın</p>
        </div>
        <?php if (isset($login_error)) : ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($login_error); ?></div>
        <?php endif; ?>
        <form method="post">
            <div class="mb-3">
                <label for="username" class="form-label">Kullanıcı Adı</label>
                <input type="text" class="form-control" id="username" name="username" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Şifre</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <button type="submit" name="login" class="btn btn-primary w-100">Giriş Yap</button>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>