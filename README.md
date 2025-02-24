# Mail Tracker

E-posta takip sistemi - E-postaların açılma durumunu ve coğrafi konumunu takip edin.
@author A. Kerem Gök

## Özellikler

- 📧 E-posta açılma takibi
- 📊 Detaylı istatistikler
  - Toplam açılma sayısı
  - Benzersiz IP sayısı
  - Günlük açılma sayısı
  - Aktif kampanya sayısı
- 🌍 Coğrafi konum takibi
  - Şehir ve ülke bazlı takip
  - Harita üzerinde görselleştirme
- 📱 Mobil uyumlu arayüz
- 🔒 Rol tabanlı yetkilendirme sistemi
  - Admin: Tam yetki
  - Editor: Kampanya yönetimi
  - Viewer: Sadece görüntüleme
- 📈 Kampanya yönetimi
  - Kampanya oluşturma ve düzenleme
  - Kampanya bazlı istatistikler
  - Hızlı takip URL oluşturma
- 🔔 Telegram bildirimleri
  - Anlık e-posta açılma bildirimleri
  - Detaylı bilgi (IP, konum, tarayıcı)

## Kurulum

### Gereksinimler

- PHP 7.4 veya üzeri
- MySQL 5.7 veya üzeri
- Composer
- Web sunucusu (Apache/Nginx)

### Adımlar

1. Projeyi klonlayın:
```bash
git clone https://github.com/username/mail-tracker.git
cd mail-tracker
```

2. Veritabanını oluşturun:
```bash
mysql -u root -p < setup.sql
```

3. Çevresel değişkenleri ayarlayın:
```bash
cp .env.example .env
```
`.env` dosyasını düzenleyin:
```
DB_HOST=localhost
DB_NAME=mail_tracker
DB_USER=root
DB_PASS=your_password

TELEGRAM_BOT_TOKEN=your_bot_token
TELEGRAM_CHAT_ID=your_chat_id

IPAPI_KEY=your_ipapi_key
```

4. Bağımlılıkları yükleyin:
```bash
composer install
```

5. Web sunucusunu yapılandırın:

Apache için (.htaccess):
```apache
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]
```

Nginx için:
```nginx
location / {
    try_files $uri $uri/ /index.php?$query_string;
}
```

6. Dizin izinlerini ayarlayın:
```bash
chmod 755 -R *
chmod 777 -R logs/
```

### İlk Kullanıcı

Varsayılan admin kullanıcısı:
- Kullanıcı adı: admin
- Şifre: admin123

İlk girişten sonra şifrenizi değiştirmeyi unutmayın!

## Kullanım

1. E-posta Takibi:
   - Kampanya oluşturun
   - Tracking kodunu kopyalayın
   - E-postanıza ekleyin (görünmez piksel veya görünür logo)
   - Açılmaları takip edin

2. Hızlı Takip:
   - "Hızlı Takip URL" oluşturun
   - Tek seferlik kullanım için idealdir
   - Otomatik olarak kampanyalara dahil edilmez

3. İstatistikler:
   - Genel istatistikleri görüntüleyin
   - Kampanya bazlı raporları inceleyin
   - Coğrafi dağılımı haritada görün

## Güvenlik

- SQL injection koruması
- XSS koruması
- CSRF koruması
- Rol tabanlı yetkilendirme
- Şifreleme ve hash kullanımı

## Lisans

Bu proje MIT lisansı altında lisanslanmıştır. Detaylar için `LICENSE` dosyasına bakın.

## Destek

Sorun bildirimi ve öneriler için Issues bölümünü kullanabilirsiniz. 