# 📧 Mail Tracker

E-posta takip sistemi - Mail açılma bildirimlerini anlık olarak takip edin.

**Geliştirici:** A. Kerem Gök

## 🚀 Özellikler

- E-posta açılma takibi
- Anlık Telegram bildirimleri
- Modern web arayüzü
- Detaylı istatistikler
- IP ve tarayıcı bilgisi takibi
- Kolay entegrasyon

## 📋 Gereksinimler

- PHP 7.4 veya üzeri
- MySQL/MariaDB
- Web sunucusu (Apache/Nginx)
- Telegram Bot API erişimi

## ⚙️ Kurulum

### 1. Veritabanı Kurulumu

1. MySQL/MariaDB veritabanınıza bağlanın
2. `setup.sql` dosyasını çalıştırın:
```sql
source setup.sql
```

### 2. Veritabanı Bağlantı Ayarları

`index.php` dosyasındaki veritabanı bağlantı bilgilerini güncelleyin:

```php
$db_host = 'localhost';
$db_name = 'mail_tracker';
$db_user = 'root';     // Veritabanı kullanıcı adınız
$db_pass = '';         // Veritabanı şifreniz
```

### 3. Telegram Bot Kurulumu

1. Telegram'da @BotFather ile yeni bir bot oluşturun:
   - Telegram'ı açın ve @BotFather ile sohbet başlatın
   - `/newbot` komutunu gönderin
   - Bot için bir isim belirleyin (örn: "Mail Tracker Bot")
   - Bot için bir kullanıcı adı belirleyin (örn: "mail_tracker_bot")
   - BotFather size bir TOKEN verecek, bu token'ı kaydedin

2. Chat ID'nizi alın:
   - Oluşturduğunuz bot ile özel mesaj başlatın
   - Bota herhangi bir mesaj gönderin
   - Tarayıcınızdan şu adresi ziyaret edin:
     ```
     https://api.telegram.org/bot<BOT_TOKEN>/getUpdates
     ```
   - Çıkan JSON yanıtında `chat` > `id` değerini bulun

3. Bot bilgilerini `index.php` dosyasına ekleyin:
```php
define('TELEGRAM_BOT_TOKEN', 'YOUR_BOT_TOKEN_HERE');
define('TELEGRAM_CHAT_ID', 'YOUR_CHAT_ID_HERE');
```

## 📝 Kullanım

### Tracking URL Oluşturma

1. Web arayüzünü açın
2. "Tracking URL Oluşturucu" bölümünden yeni bir URL alın
3. Bu URL'i e-postanıza resim olarak ekleyin:
```html
<img src="http://sizinsiteniz.com/index.php?track=TRACKING_ID" width="1" height="1" />
```

### Bildirimleri Takip Etme

- E-posta açıldığında Telegram'dan anlık bildirim alacaksınız
- Web arayüzünden tüm açılma kayıtlarını görebilirsiniz
- İstatistik kartlarından özet bilgileri takip edebilirsiniz

## 🔒 Güvenlik Notları

1. Veritabanı bilgilerinizi güvenli bir şekilde saklayın
2. Telegram bot token'ınızı gizli tutun
3. Production ortamında tüm hassas bilgileri ayrı bir config dosyasında tutun
4. IP adresi toplama konusunda KVKK gereksinimlerini göz önünde bulundurun

## 📊 Özellik Detayları

- **Anlık Bildirimler:** E-posta açıldığında şu bilgilerle anında Telegram bildirimi:
  - Tracking ID
  - IP Adresi
  - Tarayıcı bilgisi
  - Açılma zamanı

- **İstatistikler:**
  - Toplam açılma sayısı
  - Benzersiz IP sayısı
  - Günlük açılma sayısı

## 🤝 Katkıda Bulunma

1. Bu depoyu fork edin
2. Yeni bir branch oluşturun (`git checkout -b yeni-ozellik`)
3. Değişikliklerinizi commit edin (`git commit -am 'Yeni özellik eklendi'`)
4. Branch'inizi push edin (`git push origin yeni-ozellik`)
5. Pull Request oluşturun

## 📜 Lisans

Bu proje MIT lisansı altında lisanslanmıştır. Daha fazla bilgi için `LICENSE` dosyasına bakın. 