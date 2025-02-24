<?php

/**
 * Mail Tracker Application
 * @author A. Kerem Gök
 */

session_start();

// Environment yardımcısını yükle
require_once __DIR__ . '/helpers/env.php';
require_once __DIR__ . '/helpers/db.php';
require_once __DIR__ . '/helpers/telegram.php';
require_once __DIR__ . '/helpers/geolocation.php';

// Giriş kontrolü
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Yetki kontrolü
function checkPermission($required_role = 'viewer')
{
    if (!isset($_SESSION['user_id'])) return false;

    global $pdo;
    try {
        $stmt = $pdo->prepare("SELECT role FROM admins WHERE id = ? AND is_active = 1");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch();

        if (!$user) return false;

        $roles = ['viewer' => 1, 'editor' => 2, 'admin' => 3];
        return $roles[$user['role']] >= $roles[$required_role];
    } catch (PDOException $e) {
        return false;
    }
}

// Çıkış işlemi
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: login.php');
    exit;
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
        // Log kaydı oluştur
        $stmt = $pdo->prepare("INSERT INTO email_logs (tracking_id, ip_address, user_agent, opened_at) VALUES (?, ?, ?, ?)");
        $stmt->execute([$tracking_id, $ip_address, $user_agent, $timestamp]);
        $log_id = $pdo->lastInsertId();

        // Kampanya istatistiklerini güncelle
        $pdo->prepare("
            UPDATE campaigns 
            SET total_opened = total_opened + 1 
            WHERE tracking_prefix = ? AND NOW() BETWEEN start_date AND COALESCE(end_date, NOW())
        ")->execute([substr($tracking_id, 0, 8)]);

        // Coğrafi konum bilgisini al ve kaydet
        $geo_data = getGeoLocation($ip_address);
        if ($geo_data) {
            $stmt = $pdo->prepare("
                INSERT INTO geo_locations (log_id, country, city, region, latitude, longitude) 
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $log_id,
                $geo_data['country_name'] ?? null,
                $geo_data['city'] ?? null,
                $geo_data['region_name'] ?? null,
                $geo_data['latitude'] ?? null,
                $geo_data['longitude'] ?? null
            ]);
        }

        // Telegram bildirimi gönder
        $message = "📧 <b>Yeni E-posta Açılması!</b>\n\n" .
            "🔍 Tracking ID: <code>" . htmlspecialchars($tracking_id) . "</code>\n" .
            "🌐 IP Adresi: <code>" . htmlspecialchars($ip_address) . "</code>\n" .
            "🔎 Tarayıcı: " . htmlspecialchars($user_agent) . "\n" .
            "⏰ Zaman: " . htmlspecialchars($timestamp);

        if ($geo_data) {
            $message .= "\n📍 Konum: " . ($geo_data['city'] ?? '') .
                ", " . ($geo_data['country_name'] ?? '');
        }

        sendTelegramMessage($message);
    } catch (PDOException $e) {
        error_log("Log kayıt hatası: " . $e->getMessage());
    }

    exit;
}

// API endpoint'leri
if (isset($_GET['api'])) {
    header('Content-Type: application/json');

    if (!checkPermission('editor')) {
        echo json_encode(['error' => 'Yetkisiz erişim']);
        exit;
    }

    switch ($_GET['api']) {
        case 'campaigns':
            // Kampanya listesi
            if ($_SERVER['REQUEST_METHOD'] === 'GET') {
                $stmt = $pdo->query("SELECT * FROM campaigns ORDER BY created_at DESC");
                echo json_encode($stmt->fetchAll());
            }
            // Yeni kampanya oluşturma
            else if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                try {
                    $data = json_decode(file_get_contents('php://input'), true);
                    
                    $stmt = $pdo->prepare("
                        INSERT INTO campaigns (name, description, tracking_prefix, created_by)
                        VALUES (?, ?, ?, ?)
                    ");
                    $stmt->execute([
                        $data['name'],
                        $data['description'],
                        bin2hex(random_bytes(4)), // Rastgele tracking prefix
                        $_SESSION['user_id']
                    ]);
                    echo json_encode(['success' => true, 'id' => $pdo->lastInsertId()]);
                } catch (Exception $e) {
                    http_response_code(400);
                    echo json_encode(['error' => $e->getMessage()]);
                }
            }
            // Kampanya güncelleme
            else if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
                try {
                    $data = json_decode(file_get_contents('php://input'), true);
                    
                    $stmt = $pdo->prepare("
                        UPDATE campaigns 
                        SET name = ?, description = ?
                        WHERE id = ? AND created_by = ?
                    ");
                    $stmt->execute([
                        $data['name'],
                        $data['description'],
                        $data['id'],
                        $_SESSION['user_id']
                    ]);
                    echo json_encode(['success' => true]);
                } catch (Exception $e) {
                    http_response_code(400);
                    echo json_encode(['error' => $e->getMessage()]);
                }
            }
            // Kampanya silme
            else if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
                $id = $_GET['id'];
                $stmt = $pdo->prepare("DELETE FROM campaigns WHERE id = ? AND created_by = ?");
                $stmt->execute([$id, $_SESSION['user_id']]);
                echo json_encode(['success' => true]);
            }
            break;

        case 'stats':
            $stats = [
                'total_opens' => $pdo->query("SELECT COUNT(*) FROM email_logs")->fetchColumn(),
                'unique_ips' => $pdo->query("SELECT COUNT(DISTINCT ip_address) FROM email_logs")->fetchColumn(),
                'today_opens' => $pdo->query("SELECT COUNT(*) FROM email_logs WHERE DATE(opened_at) = CURDATE()")->fetchColumn(),
                'countries' => $pdo->query("SELECT country, COUNT(*) as count FROM geo_locations GROUP BY country ORDER BY count DESC LIMIT 5")->fetchAll()
            ];
            echo json_encode($stats);
            break;
    }
    exit;
}

// Ana sayfa görüntüleme - Buradan sonrası sadece giriş yapmış kullanıcılar için
?>
<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mail Tracker</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
    <link rel="stylesheet" href="index.css">
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-dark mb-4">
        <div class="container">
            <a class="navbar-brand" href="#"><i class="bi bi-envelope-check me-2"></i>Mail Tracker</a>
            <div class="user-info">
                <span class="me-2">
                    Hoş geldiniz, <?php echo htmlspecialchars($_SESSION['username']); ?>
                    (<?php echo ucfirst($_SESSION['role']); ?>)
                </span>
                <a href="?logout=1" class="logout-btn">
                    <i class="bi bi-box-arrow-right me-1"></i>Çıkış Yap
                </a>
            </div>
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
                $active_campaigns = $pdo->query("SELECT COUNT(*) FROM campaigns")->fetchColumn();
            } catch (PDOException $e) {
                $total_opens = $unique_ips = $today_opens = $active_campaigns = 0;
            }
            ?>
            <div class="col-md-3">
                <div class="card stats-card">
                    <div class="stats-number"><?php echo number_format($total_opens); ?></div>
                    <div class="stats-label">Toplam Açılma</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stats-card">
                    <div class="stats-number"><?php echo number_format($unique_ips); ?></div>
                    <div class="stats-label">Benzersiz IP</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stats-card">
                    <div class="stats-number"><?php echo number_format($today_opens); ?></div>
                    <div class="stats-label">Bugünkü Açılma</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stats-card">
                    <div class="stats-number"><?php echo number_format($active_campaigns); ?></div>
                    <div class="stats-label">Aktif Kampanya</div>
                </div>
            </div>
        </div>

        <?php if (checkPermission('editor')): ?>
            <!-- Kampanyalar -->
            <div class="row mb-4">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="card-title">
                                    <i class="bi bi-graph-up me-2"></i>Kampanyalar
                                </h5>
                                <button class="btn btn-primary" onclick="showCampaignModal()">
                                    <i class="bi bi-plus-lg me-1"></i>Yeni Kampanya
                                </button>
                            </div>
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Kampanya Adı</th>
                                            <th>Açıklama</th>
                                            <th>Tracking Prefix</th>
                                            <th>Açılma</th>
                                            <th>İşlemler</th>
                                        </tr>
                                    </thead>
                                    <tbody id="campaignsTable">
                                        <!-- JavaScript ile doldurulacak -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

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

        <!-- Harita -->
        <div class="card mb-4">
            <div class="card-body">
                <h5 class="card-title mb-3">
                    <i class="bi bi-geo-alt me-2"></i>Coğrafi Dağılım
                </h5>
                <div id="map"></div>
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
                                <th>Konum</th>
                                <th>Tarayıcı</th>
                                <th>Açılma Zamanı</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            try {
                                $stmt = $pdo->query("
                                    SELECT l.*, g.country, g.city 
                                    FROM email_logs l 
                                    LEFT JOIN geo_locations g ON l.id = g.log_id 
                                    ORDER BY l.opened_at DESC 
                                    LIMIT 50
                                ");
                                while ($row = $stmt->fetch()) {
                                    echo "<tr>";
                                    echo "<td><span class='badge bg-primary'>" . htmlspecialchars($row['tracking_id']) . "</span></td>";
                                    echo "<td>" . htmlspecialchars($row['ip_address']) . "</td>";
                                    echo "<td>" . ($row['city'] ? htmlspecialchars($row['city'] . ', ' . $row['country']) : '-') . "</td>";
                                    echo "<td><small class='text-muted'>" . htmlspecialchars($row['user_agent']) . "</small></td>";
                                    echo "<td>" . htmlspecialchars(date('d.m.Y H:i:s', strtotime($row['opened_at']))) . "</td>";
                                    echo "</tr>";
                                }
                            } catch (PDOException $e) {
                                echo "<tr><td colspan='5' class='text-center text-muted'>Veri çekme hatası oluştu.</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Kampanya Modal -->
    <div class="modal fade" id="campaignModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Kampanya Yönetimi</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="campaignForm">
                        <input type="hidden" id="campaignId">
                        <div class="mb-3">
                            <label class="form-label">Kampanya Adı</label>
                            <input type="text" class="form-control" id="campaignName" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Açıklama</label>
                            <textarea class="form-control" id="campaignDescription"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                    <button type="button" class="btn btn-primary" onclick="saveCampaign()">Kaydet</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Tracking Link Modal -->
    <div class="modal fade" id="trackingLinkModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Tracking HTML Kodu</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">1x1 Görünmez Piksel</label>
                        <div class="code-block">
                            <pre id="invisibleCode" class="bg-light p-3 rounded"></pre>
                            <button class="copy-btn" onclick="copyCode('invisibleCode')">
                                <i class="bi bi-clipboard me-1"></i>Kopyala
                            </button>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Görünür Logo/İmza</label>
                        <div class="code-block">
                            <pre id="visibleCode" class="bg-light p-3 rounded"></pre>
                            <button class="copy-btn" onclick="copyCode('visibleCode')">
                                <i class="bi bi-clipboard me-1"></i>Kopyala
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
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

        // Harita başlatma
        const map = L.map('map').setView([0, 0], 2);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);

        // Konum verilerini haritaya ekle
        <?php
        try {
            $stmt = $pdo->query("
                SELECT latitude, longitude, COUNT(*) as count 
                FROM geo_locations 
                WHERE latitude IS NOT NULL 
                GROUP BY latitude, longitude
            ");
            while ($point = $stmt->fetch()) {
                echo "L.circle([{$point['latitude']}, {$point['longitude']}], {
                    color: 'red',
                    fillColor: '#f03',
                    fillOpacity: 0.5,
                    radius: {$point['count']} * 5000
                }).addTo(map);\n";
            }
        } catch (PDOException $e) {
            // Hata durumunda haritada nokta gösterme
        }
        ?>

        // Tracking kodunu göster
        function showTrackingCode(prefix) {
            const baseUrl = window.location.href.split('?')[0];
            const trackingId = prefix + Date.now().toString(16);
            const trackingUrl = baseUrl + '?track=' + trackingId;
            
            // Görünmez piksel kodu
            document.getElementById('invisibleCode').textContent = 
                `<img src="${trackingUrl}" width="1" height="1" style="display:none">`;
            
            // Görünür logo kodu
            document.getElementById('visibleCode').textContent = 
                `<img src="${trackingUrl}" width="150" alt="Logo">`;
            
            new bootstrap.Modal(document.getElementById('trackingLinkModal')).show();
        }

        // Kodu kopyala
        function copyCode(elementId) {
            const code = document.getElementById(elementId).textContent;
            navigator.clipboard.writeText(code).then(() => {
                const button = document.querySelector(`#${elementId}`).nextElementSibling;
                button.innerHTML = '<i class="bi bi-check2 me-1"></i>Kopyalandı';
                setTimeout(() => {
                    button.innerHTML = '<i class="bi bi-clipboard me-1"></i>Kopyala';
                }, 2000);
            });
        }

        // Kampanya listesini yükle
        function loadCampaigns() {
            fetch('?api=campaigns')
                .then(response => response.json())
                .then(campaigns => {
                    const tbody = document.getElementById('campaignsTable');
                    tbody.innerHTML = '';
                    
                    campaigns.forEach(campaign => {
                        tbody.innerHTML += `
                            <tr>
                                <td>${escapeHtml(campaign.name)}</td>
                                <td>${escapeHtml(campaign.description || '')}</td>
                                <td><code style="cursor: pointer" onclick="showTrackingCode('${campaign.tracking_prefix}')">${campaign.tracking_prefix}</code></td>
                                <td>${campaign.total_opened} / ${campaign.total_sent}</td>
                                <td>
                                    <button class="btn btn-sm btn-outline-primary" onclick="editCampaign(${campaign.id})">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger" onclick="deleteCampaign(${campaign.id})">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        `;
                    });
                });
        }

        // Kampanya kaydet
        function saveCampaign() {
            const form = document.getElementById('campaignForm');
            if (!form.checkValidity()) {
                form.reportValidity();
                return;
            }

            const id = document.getElementById('campaignId').value;
            const data = {
                name: document.getElementById('campaignName').value,
                description: document.getElementById('campaignDescription').value
            };

            const method = id ? 'PUT' : 'POST';
            if (id) data.id = id;

            fetch('?api=campaigns' + (id ? '&id=' + id : ''), {
                method: method,
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    bootstrap.Modal.getInstance(document.getElementById('campaignModal')).hide();
                    loadCampaigns();
                    showSuccess('Kampanya başarıyla ' + (id ? 'güncellendi' : 'oluşturuldu') + '.');
                } else if (result.error) {
                    showError(result.error);
                }
            })
            .catch(error => {
                showError('Bir hata oluştu: ' + error.message);
            });
        }

        // Hata mesajı göster
        function showError(message) {
            const alertDiv = document.createElement('div');
            alertDiv.className = 'alert alert-danger alert-dismissible fade show';
            alertDiv.innerHTML = `
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            document.querySelector('.modal-body').insertBefore(alertDiv, document.getElementById('campaignForm'));
        }

        // Başarı mesajı göster
        function showSuccess(message) {
            const alertDiv = document.createElement('div');
            alertDiv.className = 'alert alert-success alert-dismissible fade show position-fixed top-0 start-50 translate-middle-x mt-3';
            alertDiv.style.zIndex = '9999';
            alertDiv.innerHTML = `
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            document.body.appendChild(alertDiv);
            setTimeout(() => alertDiv.remove(), 3000);
        }

        // Kampanya modalını göster
        function showCampaignModal(campaign = null) {
            // Önceki hata mesajlarını temizle
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => alert.remove());

            document.getElementById('campaignId').value = campaign ? campaign.id : '';
            document.getElementById('campaignName').value = campaign ? campaign.name : '';
            document.getElementById('campaignDescription').value = campaign ? campaign.description : '';
            
            new bootstrap.Modal(document.getElementById('campaignModal')).show();
        }

        // Kampanya düzenle
        function editCampaign(id) {
            fetch('?api=campaigns')
                .then(response => response.json())
                .then(campaigns => {
                    const campaign = campaigns.find(c => c.id === id);
                    if (campaign) showCampaignModal(campaign);
                });
        }

        // Kampanya sil
        function deleteCampaign(id) {
            if (confirm('Bu kampanyayı silmek istediğinizden emin misiniz?')) {
                fetch('?api=campaigns&id=' + id, {method: 'DELETE'})
                    .then(response => response.json())
                    .then(result => {
                        if (result.success) loadCampaigns();
                    });
            }
        }

        // Yardımcı fonksiyonlar
        function getStatus(startDate, endDate) {
            const now = new Date();
            const start = new Date(startDate);
            const end = endDate ? new Date(endDate) : null;
            
            if (now < start) return 'scheduled';
            if (!end || now <= end) return 'active';
            return 'ended';
        }

        function getStatusText(status) {
            return {
                'scheduled': 'Planlandı',
                'active': 'Aktif',
                'ended': 'Bitti'
            }[status];
        }

        function formatDate(date) {
            return new Date(date).toLocaleString('tr-TR');
        }

        function escapeHtml(str) {
            if (!str) return '';
            return str
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;")
                .replace(/"/g, "&quot;")
                .replace(/'/g, "&#039;");
        }

        // Sayfa yüklendiğinde kampanyaları listele
        document.addEventListener('DOMContentLoaded', loadCampaigns);
    </script>
</body>

</html>