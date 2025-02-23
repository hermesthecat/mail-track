# 📧 Mail Tracker

E-posta takip sistemi - Mail açılma bildirimlerini anlık olarak takip edin.

**Geliştirici:** A. Kerem Gök

## 📌 İçindekiler

- [Özellikler](#-özellikler)
- [Gereksinimler](#-gereksinimler)
- [Kurulum](#️-kurulum)
- [Kullanım](#-kullanım)
- [Güvenlik](#-güvenlik-notları)
- [Özellik Detayları](#-özellik-detayları)
- [SSS](#-sık-sorulan-sorular)
- [Katkıda Bulunma](#-katkıda-bulunma)
- [Lisans](#-lisans)

## 🚀 Özellikler

### Temel Özellikler
- ✉️ E-posta açılma takibi
- 🔔 Anlık Telegram bildirimleri
- 🎨 Modern ve responsive web arayüzü
- 📊 Detaylı istatistikler ve raporlama
- 🔍 IP ve tarayıcı bilgisi takibi
- 🔐 Güvenli giriş sistemi
- 📱 Mobil uyumlu tasarım
- 🔄 Kolay entegrasyon
- 📈 Gerçek zamanlı istatistikler

### Yeni Özellikler
- 📝 Hazır E-posta Şablonları
- 📍 Coğrafi Konum Takibi
- 📊 Kampanya Yönetimi
- 👥 Çoklu Kullanıcı Desteği
- 🔑 Rol Tabanlı Yetkilendirme
- 📱 API Desteği (yakında)
- 📊 Gelişmiş Raporlama
- 🌍 Çoklu Dil Desteği (yakında)

## 📋 Gereksinimler

- 🔧 PHP 7.4 veya üzeri
- 📦 MySQL/MariaDB
- 🌐 Web sunucusu (Apache/Nginx)
- 🤖 Telegram Bot API erişimi
- 📨 SMTP sunucusu (opsiyonel)

## ⚙️ Kurulum

### 1. Dosyaları Yükleme

```bash
# Projeyi klonlayın
git clone https://github.com/kullaniciadi/mail-tracker.git

# Proje dizinine girin
cd mail-tracker

# Gerekli izinleri ayarlayın
chmod 755 .
chmod 644 *.php
```

### 2. Veritabanı Kurulumu

1. MySQL/MariaDB veritabanınıza bağlanın
2. `setup.sql` dosyasını çalıştırın:
```sql
source setup.sql
```

### 3. Veritabanı Bağlantı Ayarları

`index.php` dosyasındaki veritabanı bağlantı bilgilerini güncelleyin:

```php
$db_host = 'localhost';
$db_name = 'mail_tracker';
$db_user = 'root';     // Veritabanı kullanıcı adınız
$db_pass = '';         // Veritabanı şifreniz
```

### 4. Telegram Bot Kurulumu

1. **Bot Oluşturma:**
   - Telegram'da [@BotFather](https://t.me/botfather) ile sohbet başlatın
   - `/newbot` komutunu gönderin
   - Bot için bir isim belirleyin (örn: "Mail Tracker Bot")
   - Bot için bir kullanıcı adı belirleyin (örn: "mail_tracker_bot")
   - Size verilen TOKEN'ı kaydedin

2. **Chat ID Alma:**
   - Oluşturduğunuz bot ile özel mesaj başlatın ("/start")
   - Bota herhangi bir mesaj gönderin
   - Şu URL'i ziyaret edin:
     ```
     https://api.telegram.org/bot<BOT_TOKEN>/getUpdates
     ```
   - JSON yanıtında `chat` > `id` değerini bulun

3. **Bot Ayarlarını Yapılandırma:**
   - `index.php` dosyasını açın
   - Bot bilgilerini ekleyin:
     ```php
     define('TELEGRAM_BOT_TOKEN', 'YOUR_BOT_TOKEN_HERE');
     define('TELEGRAM_CHAT_ID', 'YOUR_CHAT_ID_HERE');
     ```

### 5. Giriş Bilgileri

**Varsayılan Hesap:**
- 👤 Kullanıcı adı: `admin`
- 🔑 Şifre: `admin123`

**Şifre Değiştirme:**
1. Veritabanına bağlanın
2. Aşağıdaki SQL komutunu çalıştırın:
```sql
-- Yeni şifre için güvenli hash oluşturma
UPDATE admins 
SET password = '$2y$10$' || SHA2('YeniŞifreniz', 256) 
WHERE username = 'admin';
```

## 📝 Kullanım

### 1. Sisteme Giriş

1. Web arayüzünü açın
2. Kullanıcı adı ve şifrenizle giriş yapın
3. Giriş yaptıktan sonra dashboard'a yönlendirileceksiniz

### 2. Tracking URL Oluşturma

1. Dashboard'da "Tracking URL Oluşturucu" bölümüne gidin
2. "Yeni URL Oluştur" butonuna tıklayın
3. Oluşturulan URL'i e-postanıza ekleyin:

```html
<!-- Görünmez tracking pixel -->
<img src="http://sizinsiteniz.com/index.php?track=TRACKING_ID" 
     width="1" 
     height="1" 
     style="display:none">

<!-- veya -->

<!-- Görünür logo/imza olarak -->
<img src="http://sizinsiteniz.com/index.php?track=TRACKING_ID" 
     width="150" 
     alt="Logo">
```

### 3. Bildirimleri Takip Etme

- 📱 Telegram'dan anlık bildirimler
- 📊 Web arayüzünden detaylı istatistikler
- 📈 Günlük/haftalık/aylık raporlar
- 🔍 Detaylı açılma bilgileri

## 🔒 Güvenlik Notları

1. **Veritabanı Güvenliği:**
   - Güçlü şifreler kullanın
   - Düzenli yedek alın
   - Gereksiz yetkileri kaldırın

2. **API Güvenliği:**
   - Telegram token'ını gizli tutun
   - Rate limiting uygulayın
   - IP kısıtlaması ekleyin

3. **Genel Güvenlik:**
   - SSL/TLS kullanın
   - Güvenlik duvarı kurun
   - Düzenli güncelleme yapın

## 💡 Sık Sorulan Sorular

1. **Mail açılma bildirimi almıyorum?**
   - Telegram bot ayarlarını kontrol edin
   - Internet bağlantınızı kontrol edin
   - Log dosyalarını inceleyin

2. **Tracking çalışmıyor?**
   - URL'in doğru olduğundan emin olun
   - E-posta istemcisinin resimleri gösterdiğinden emin olun
   - Sunucu erişimini kontrol edin

3. **Giriş yapamıyorum?**
   - Veritabanı bağlantısını kontrol edin
   - Şifrenizi sıfırlayın
   - Hata loglarını inceleyin

## 🤝 Katkıda Bulunma

1. Bu depoyu fork edin
2. Feature branch oluşturun (`git checkout -b yeni-ozellik`)
3. Değişikliklerinizi commit edin (`git commit -am 'Yeni özellik: XYZ'`)
4. Branch'inizi push edin (`git push origin yeni-ozellik`)
5. Pull Request oluşturun

## 📜 Lisans

Bu proje MIT lisansı altında lisanslanmıştır. Detaylar için `LICENSE` dosyasına bakın.

## 📞 İletişim

- 📧 E-posta: [eposta@adresiniz.com](mailto:eposta@adresiniz.com)
- 🌐 Website: [www.siteniz.com](https://www.siteniz.com)
- 💬 Telegram: [@kullaniciadiniz](https://t.me/kullaniciadiniz)

## 📊 Özellik Detayları

### 📝 E-posta Şablonları
- Hazır HTML şablonları
- Özelleştirilebilir tasarımlar
- Kategori bazlı organizasyon
- Otomatik tracking pixel entegrasyonu
- Şablon önizleme
- Drag & Drop editör (yakında)

### 📍 Coğrafi Konum Takibi
- Ülke bazlı takip
- Şehir ve bölge bilgisi
- Harita üzerinde görselleştirme
- Konum bazlı raporlama
- IP bazlı otomatik konum tespiti

### 📊 Kampanya Yönetimi
- Kampanya bazlı takip
- Başlangıç/bitiş tarihi
- Açılma oranları
- Kampanya performans analizi
- Otomatik raporlama
- A/B testi desteği (yakında)

### 👥 Kullanıcı Yönetimi
- Rol tabanlı yetkilendirme (Admin, Editör, İzleyici)
- Kullanıcı aktivite logları
- Güvenli şifre politikası
- İki faktörlü doğrulama (yakında)
- Oturum yönetimi 