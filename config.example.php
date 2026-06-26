<?php
/**
 * config.example.php — Ornek ayar dosyasi
 * --------------------------------------------------
 * Bu dosyayi kopyalayip "config.php" olarak kaydet ve kendi degerlerini gir.
 * config.php GitHub'a YUKLENMEZ (.gitignore'da). Anahtarini guvende tutar;
 * ai_recipe.php'yi tekrar yukleseniz bile config.php silinmedigi icin anahtar kalir.
 *
 * Gemini API anahtari al: https://aistudio.google.com/apikey  (AIza... ile baslar)
 */

return [
    'gemini_api_key' => 'AIza_BURAYA_KENDI_ANAHTARIN',
    // Kota sorunu olursa modeli degistir: 'gemini-2.0-flash' veya 'gemini-2.5-flash'
    'gemini_model'   => 'gemini-2.0-flash',
];
