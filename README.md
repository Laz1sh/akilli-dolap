# Akilli Dolap & AI Sef

**Canli Demo:** https://akillidolap.infinityfreeapp.com/login.php

Cok kullanicili bir web uygulamasi: her kullanici **kayit olup giris yapar**, kendi dolabini yonetir ve dolabindaki malzemelere gore **yapay zeka (Google Gemini)** ile yemek tarifi alir. "Bu tarifi yaptim" dediginde kullanilan malzemeler dolabindan otomatik dusulur.

## Kullanilan Teknolojiler
- **Backend:** PHP (Native) + PDO
- **Veritabani:** MySQL
- **Kimlik dogrulama:** `password_hash` / `password_verify` + PHP Session
- **Frontend:** Vanilla JavaScript (ES6+, `fetch`/AJAX)
- **Arayuz:** Tailwind CSS (CDN)
- **AI:** Google Gemini API

## Dosyalar
| Dosya | Gorevi |
|-------|--------|
| `database.sql` | `users` + `inventory` tablolarini olusturur |
| `db.php` | PDO baglantisi + oturum (session) baslatma |
| `auth.php` | Giris kontrol yardimcilari (require_login vb.) |
| `register.php` | Kayit ol sayfasi (sifre hash'lenir) |
| `login.php` | Giris yap sayfasi |
| `logout.php` | Cikis (oturumu kapatir) |
| `manage_inventory.php` | Malzeme ekle / listele / sil (kullaniciya ozel) |
| `ai_recipe.php` | Gemini ile tarif uretir (anahtar sunucuda) |
| `consume.php` | "Yaptim" -> malzemeleri duser |
| `index.php` | Uc kolonlu ana arayuz (giris gerektirir) |

## Cok Kullanicili Yapi
Her kullanicinin verisi **ayri veritabani degil**, tek veritabaninda `inventory.user_id` ile ayrilir. Bir kullanici yalnizca kendi malzemelerini gorur/degistirir.

## Yerel Kurulum (XAMPP)
1. Klasoru XAMPP'in `htdocs` klasorune kopyala (`htdocs/akilli-dolap`).
2. XAMPP'ta **Apache** ve **MySQL**'i baslat.
3. `http://localhost/phpmyadmin` -> **SQL** -> `database.sql` icerigini calistir.
4. Gerekirse `db.php`'deki baglanti bilgilerini duzenle (XAMPP varsayilan: `root`, sifre bos).
5. `ai_recipe.php` icindeki `YOUR_GEMINI_API_KEY` yerine kendi Gemini anahtarini yaz. *(Anahtarsiz da calisir -> demo tarif doner.)*
6. Ac: `http://localhost/akilli-dolap/login.php` -> Kayit ol -> kullan.

## Canli Yayin (hocanin tiklayacagi link)
GitHub PHP calistirmaz; canli link icin ucretsiz PHP+MySQL hostinge (or. InfinityFree) yukle. Adimlar `CANLI-YAYIN.txt` dosyasinda.

## Gemini API Anahtari
- Ucretsiz al: https://aistudio.google.com/apikey
- Anahtari **yalnizca** sunucudaki `ai_recipe.php`'ye yaz.
- Anahtari **GitHub'a yukleme.** Kodda `YOUR_GEMINI_API_KEY` olarak kalsin.

## Ozellikler
- Kayit / giris / cikis (sifreler hash'li)
- Kullaniciya ozel dolap (PDO prepared statement, SQL injection korumasi)
- API anahtari yalnizca sunucuda
- AJAX ile sayfa yenilemeden malzeme ekleme/silme
- Tarif uretiminde spinner
- "Bu Tarifi Yaptim!" -> malzeme dusme + aninda guncelleme
