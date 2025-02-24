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
  - Responsive tasarım
  - DataTables entegrasyonu
  - Dinamik tablo yapısı
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
- Web sunucusu (Apache/Nginx)

### Adımlar

1. Projeyi klonlayın:
```bash
git clone https://github.com/hermesthecat/mail-track.git
cd mail-track
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

### İlk Kullanıcı

Varsayılan admin kullanıcısı:
- Kullanıcı adı: admin
- Şifre: admin123

⚠️ İlk girişten sonra şifrenizi değiştirmeyi unutmayın!

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

- Veri Güvenliği
  - SQL injection koruması (PDO prepared statements)
  - XSS koruması (HTML escaping)
  - CSRF koruması (token doğrulama)
  - Güvenli şifre hash'leme (password_hash)

- Erişim Kontrolü
  - Rol tabanlı yetkilendirme sistemi
  - Session yönetimi ve doğrulama
  - IP bazlı erişim kısıtlama desteği

## Lisans

Bu proje MIT lisansı altında lisanslanmıştır. Detaylar için `LICENSE` dosyasına bakın.

## Destek

Sorun bildirimi ve öneriler için Issues bölümünü kullanabilirsiniz. 