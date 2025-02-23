<?php
/**
 * Mail Tracker Application
 * @author A. Kerem Gök
 */

// Veritabanı bağlantı bilgileri
$db_host = 'localhost';
$db_name = 'mail_tracker';
$db_user = 'root';
$db_pass = '';

try {
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    error_log("Veritabanı bağlantı hatası: " . $e->getMessage());
}

// Tracking pixel isteği kontrolü
if (isset($_GET['track'])) {
    header('Content-Type: image/gif');
    
    // 1x1 şeffaf GIF
    echo base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7');
    
    // Log bilgilerini kaydet
    $tracking_id = $_GET['track'];
    $ip_address = $_SERVER['REMOTE_ADDR'];
    $user_agent = $_SERVER['HTTP_USER_AGENT'];
    $timestamp = date('Y-m-d H:i:s');
    
    try {
        $stmt = $pdo->prepare("INSERT INTO email_logs (tracking_id, ip_address, user_agent, opened_at) VALUES (?, ?, ?, ?)");
        $stmt->execute([$tracking_id, $ip_address, $user_agent, $timestamp]);
    } catch(PDOException $e) {
        error_log("Log kayıt hatası: " . $e->getMessage());
    }
    
    exit;
}

// Ana sayfa görüntüleme
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mail Tracker</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        :root {
            --primary-color: #2563eb;
            --secondary-color: #1e40af;
        }
        
        body {
            font-family: 'Inter', -apple-system, sans-serif;
            background-color: #f8f9fa;
            color: #1a1a1a;
        }
        
        .navbar {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            padding: 1rem;
        }
        
        .navbar-brand {
            color: white !important;
            font-weight: 600;
            font-size: 1.5rem;
        }
        
        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
            transition: transform 0.2s;
        }
        
        .card:hover {
            transform: translateY(-2px);
        }
        
        .tracking-url-card {
            background: linear-gradient(135deg, #ffffff, #f8f9fa);
        }
        
        .url-display {
            background-color: #e9ecef;
            padding: 1rem;
            border-radius: 8px;
            font-family: monospace;
            position: relative;
        }
        
        .copy-btn {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            background: var(--primary-color);
            color: white;
            border: none;
            padding: 5px 15px;
            border-radius: 5px;
            cursor: pointer;
        }
        
        .table {
            background: white;
            border-radius: 12px;
            overflow: hidden;
        }
        
        .table th {
            background-color: #f8f9fa;
            border-bottom: 2px solid #dee2e6;
            color: #495057;
        }
        
        .badge {
            font-size: 0.8rem;
            padding: 0.35rem 0.65rem;
        }
        
        .stats-card {
            text-align: center;
            padding: 1.5rem;
        }
        
        .stats-number {
            font-size: 2rem;
            font-weight: 600;
            color: var(--primary-color);
        }
        
        .stats-label {
            color: #6c757d;
            font-size: 0.9rem;
            margin-top: 0.5rem;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark mb-4">
        <div class="container">
            <a class="navbar-brand" href="#"><i class="bi bi-envelope-check me-2"></i>Mail Tracker</a>
        </div>
    </nav>

    <div class="container py-4">
        <!-- İstatistik Kartları -->
        <div class="row mb-4">
            <?php
            try {
                $total_opens = $pdo->query("SELECT COUNT(*) FROM email_logs")->fetchColumn();
                $unique_ips = $pdo->query("SELECT COUNT(DISTINCT ip_address) FROM email_logs")->fetchColumn();
                $today_opens = $pdo->query("SELECT COUNT(*) FROM email_logs WHERE DATE(opened_at) = CURDATE()")->fetchColumn();
            } catch(PDOException $e) {
                $total_opens = $unique_ips = $today_opens = 0;
            }
            ?>
            <div class="col-md-4">
                <div class="card stats-card">
                    <div class="stats-number"><?php echo number_format($total_opens); ?></div>
                    <div class="stats-label">Toplam Açılma</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card stats-card">
                    <div class="stats-number"><?php echo number_format($unique_ips); ?></div>
                    <div class="stats-label">Benzersiz IP</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card stats-card">
                    <div class="stats-number"><?php echo number_format($today_opens); ?></div>
                    <div class="stats-label">Bugünkü Açılma</div>
                </div>
            </div>
        </div>

        <!-- Tracking URL Kartı -->
        <div class="card tracking-url-card mb-4">
            <div class="card-body">
                <h5 class="card-title mb-3">
                    <i class="bi bi-link-45deg me-2"></i>Tracking URL Oluşturucu
                </h5>
                <div class="url-display">
                    <?php
                    $example_tracking_id = bin2hex(random_bytes(8));
                    $tracking_url = "http://" . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'] . "?track=" . $example_tracking_id;
                    echo htmlspecialchars($tracking_url);
                    ?>
                    <button class="copy-btn" onclick="copyUrl(this)">
                        <i class="bi bi-clipboard me-1"></i>Kopyala
                    </button>
                </div>
            </div>
        </div>

        <!-- Log Tablosu -->
        <div class="card">
            <div class="card-body">
                <h5 class="card-title mb-3">
                    <i class="bi bi-clock-history me-2"></i>Son E-posta Açılmaları
                </h5>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Tracking ID</th>
                                <th>IP Adresi</th>
                                <th>Tarayıcı</th>
                                <th>Açılma Zamanı</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            try {
                                $stmt = $pdo->query("SELECT * FROM email_logs ORDER BY opened_at DESC LIMIT 50");
                                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                    echo "<tr>";
                                    echo "<td><span class='badge bg-primary'>" . htmlspecialchars($row['tracking_id']) . "</span></td>";
                                    echo "<td>" . htmlspecialchars($row['ip_address']) . "</td>";
                                    echo "<td><small class='text-muted'>" . htmlspecialchars($row['user_agent']) . "</small></td>";
                                    echo "<td>" . htmlspecialchars(date('d.m.Y H:i:s', strtotime($row['opened_at']))) . "</td>";
                                    echo "</tr>";
                                }
                            } catch(PDOException $e) {
                                echo "<tr><td colspan='4' class='text-center text-muted'>Veri çekme hatası oluştu.</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function copyUrl(button) {
            const urlText = button.parentElement.textContent.trim();
            navigator.clipboard.writeText(urlText).then(() => {
                button.innerHTML = '<i class="bi bi-check2 me-1"></i>Kopyalandı';
                setTimeout(() => {
                    button.innerHTML = '<i class="bi bi-clipboard me-1"></i>Kopyala';
                }, 2000);
            });
        }
    </script>
</body>
</html>
