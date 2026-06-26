# Akilli Dolap & AI Sef

Dolaptaki malzemelere gore yapay zeka ile yemek tarifi oneren cok kullanicili web uygulamasi.

**Canli Demo:** https://akillidolap.infinityfreeapp.com/login.php

Kullanici kayit olup giris yapar, kendi dolabini yonetir; Google Gemini dolaptaki malzemelere gore tarif onerir. "Bu tarifi yaptim" dendiginde kullanilan malzemeler dolaptan otomatik dusulur ve yapilan tarifler gecmiste saklanir.

## Ozellikler
- Kayit / giris / cikis (sifreler `password_hash` ile saklanir)
- Kullaniciya ozel dolap (PDO prepared statement ile SQL injection korumasi)
- Yapay zeka tarif onerisi (ogune gore: kahvalti, aksam yemegi, cay saati...)
- Belirli bir yemek isteme + eksik malzemeleri listeleme
- "Baska tarif oner" ile farkli oneriler
- Gecmis tarifler: goruntule, sil, paylas
- "Bu tarifi yaptim" -> malzeme dusme + aninda guncelleme

## Teknolojiler
- **Backend:** PHP (Native) + PDO
- **Veritabani:** MySQL
- **Kimlik dogrulama:** `password_hash` / `password_verify` + PHP Session
- **Frontend:** Vanilla JavaScript (ES6+, fetch/AJAX)
- **Arayuz:** Tailwind CSS
- **AI:** Google Gemini API

## Dosya Yapisi
| Dosya | Gorevi |
|-------|--------|
| `database.sql` | `users`, `inventory`, `recipes` tablolarini olusturur |
| `db.php` | PDO baglantisi + oturum (session) baslatma |
| `auth.php` | Giris kontrol yardimcilari (require_login vb.) |
| `register.php` | Kayit sayfasi (sifre hash'lenir) |
| `login.php` | Giris sayfasi |
| `logout.php` | Cikis (oturumu kapatir) |
| `manage_inventory.php` | Malzeme ekle / listele / sil (kullaniciya ozel) |
| `ai_recipe.php` | Gemini ile tarif uretimi |
| `consume.php` | "Yaptim" -> malzemeleri duser, tarifi gecmise kaydeder |
| `recipes.php` | Gecmis tarifleri listele / sil |
| `index.php` | Uc kolonlu ana arayuz |
| `config.example.php` | Ornek ayar dosyasi |

## Cok Kullanicili Yapi
Her kullanicinin verisi ayri veritabani degil, tek veritabaninda `user_id` ile ayrilir. Bir kullanici yalnizca kendi malzeme ve tariflerini gorur/degistirir.

## Kurulum (XAMPP)
1. Projeyi XAMPP'in `htdocs` klasorune koy.
2. Apache ve MySQL'i baslat.
3. phpMyAdmin'de `database.sql`'i calistir.
4. `db.php`'deki veritabani bilgilerini ortamina gore ayarla (XAMPP varsayilan: `root`, sifre bos).
5. `config.example.php`'yi `config.php` olarak kopyala ve Gemini API anahtarini gir. (Anahtar girilmezse demo tarif doner.)
6. `login.php`'yi ac, kayit ol ve kullan.

## Yapilandirma
Gemini API anahtari ucretsiz alinir: https://aistudio.google.com/apikey
Anahtar `config.php` icine yazilir; bu dosya surum kontrolune dahil edilmez.
