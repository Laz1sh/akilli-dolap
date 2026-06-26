<?php
/**
 * ai_recipe.php — AI Sef (Gemini ile tarif uretimi)
 * --------------------------------------------------
 * Dolaptaki malzemeleri okur, Gemini API'sine gonderir,
 * donen tarifi JSON olarak verir.
 *
 * GUVENLIK: API anahtari SADECE burada (sunucuda) durur.
 *           Tarayiciya / JavaScript'e asla gonderilmez.
 *
 * Anahtari al: https://aistudio.google.com/apikey
 * Asagidaki GEMINI_API_KEY degerini kendi anahtarinla degistir.
 * Anahtar bos birakilirsa "DEMO MODU" devreye girer (ornek tarif doner),
 * boylece anahtar olmadan da arayuz test edilebilir.
 */

require __DIR__ . '/auth.php';
require_login_api();
header('Content-Type: application/json; charset=utf-8');

// Ayarlar: config.php VARSA oradan okunur (anahtar orada durur, GitHub'a gitmez,
// dosyalari tekrar yukleseniz bile silinmez). Yoksa asagidaki varsayilanlar kullanilir.
$GEMINI_API_KEY = 'YOUR_GEMINI_API_KEY';
$GEMINI_MODEL   = 'gemini-2.0-flash';
if (is_file(__DIR__ . '/config.php')) {
    $cfg = require __DIR__ . '/config.php';
    if (!empty($cfg['gemini_api_key'])) { $GEMINI_API_KEY = $cfg['gemini_api_key']; }
    if (!empty($cfg['gemini_model']))   { $GEMINI_MODEL   = $cfg['gemini_model']; }
}

// 1) Giris yapan kullanicinin dolabindaki malzemeleri cek
$stmt = $pdo->prepare('SELECT name, quantity, unit FROM inventory WHERE user_id = :uid ORDER BY name');
$stmt->execute([':uid' => current_user_id()]);
$items = $stmt->fetchAll();

if (!$items) {
    echo json_encode(['success' => false, 'error' => 'Dolabiniz bos. Once malzeme ekleyin.']);
    exit;
}

$ingredientList = implode(', ', array_map(
    fn($i) => "{$i['name']} ({$i['quantity']} {$i['unit']})",
    $items
));

// "Baska tarif oner" icin: daha once onerilen tarifleri haric tut
$body    = json_decode(file_get_contents('php://input'), true);
$exclude = (isset($body['exclude']) && is_array($body['exclude'])) ? $body['exclude'] : [];
$excludeNote = '';
if ($exclude) {
    $list = implode(', ', array_map(fn($t) => (string) $t, $exclude));
    $excludeNote = "Daha once sunlari onerdin; BUNLARDAN TAMAMEN FARKLI, baska bir yemek oner (ayni yemegi tekrar onerme): $list";
}

// 2) Anahtar yoksa DEMO tarif dondur
if ($GEMINI_API_KEY === 'YOUR_GEMINI_API_KEY' || $GEMINI_API_KEY === '') {
    echo json_encode([
        'success' => true,
        'demo'    => true,
        'recipe'  => [
            'title'       => 'Pratik Menemen (Demo)',
            'description' => 'API anahtari girilmedigi icin ornek bir tarif gosteriliyor. '
                           . 'Gercek AI tarifleri icin ai_recipe.php icine Gemini anahtarini ekleyin.',
            'ingredients' => [
                ['name' => 'Yumurta',  'quantity' => 3, 'unit' => 'adet'],
                ['name' => 'Domates',  'quantity' => 2, 'unit' => 'adet'],
                ['name' => 'Sogan',    'quantity' => 1, 'unit' => 'adet'],
            ],
            'steps' => [
                'Sogani dograyip tavada kavurun.',
                'Domatesleri ekleyip suyunu cekene kadar pisirin.',
                'Yumurtalari kirip karistirin, tuz ve baharatla servis edin.',
            ],
        ],
    ]);
    exit;
}

// 3) Gemini'ye gonderilecek istem (prompt)
$prompt = <<<PROMPT
Sen bir Turk ascisisin. Kullanicinin dolabinda su malzemeler var: $ingredientList
$excludeNote

Gorevin: Bu malzemelere gore pratik bir yemek tarifi oner.
- Oncelik: mumkunse SADECE dolaptaki malzemelerle (ve tuz/su/yag gibi temel seylerle) yapilabilen bir tarif sec.
- Eger cok daha guzel/tam bir tarif icin sadece 1-2 ek malzeme gerekiyorsa, o tarifi onerebilirsin; eksik olanlari "missing" listesinde belirt ve aciklamada "su malzemeleri de alirsan bunu yapabilirsin" tarzinda kisaca soyle.
- "ingredients": SADECE dolaptan kullanilan malzemeler ve kullanilan miktar.
- "missing": dolapta OLMAYAN ama tarif icin gereken malzemeler. Hicbiri yoksa bos liste [].

Cevabi SADECE su JSON formatinda ver, baska hicbir metin ekleme:
{
  "title": "Yemek adi",
  "description": "Kisa aciklama (eksik malzeme varsa burada belirt)",
  "ingredients": [{"name": "Malzeme adi", "quantity": sayi, "unit": "birim"}],
  "missing": [{"name": "Eksik malzeme", "quantity": sayi, "unit": "birim"}],
  "steps": ["1. adim", "2. adim"]
}
PROMPT;

$payload = [
    'contents' => [[ 'parts' => [[ 'text' => $prompt ]] ]],
    'generationConfig' => [ 'response_mime_type' => 'application/json' ],
];

$url = "https://generativelanguage.googleapis.com/v1beta/models/"
     . $GEMINI_MODEL . ":generateContent?key=" . $GEMINI_API_KEY;

$ch = curl_init($url);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST           => true,
    CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
    CURLOPT_POSTFIELDS     => json_encode($payload),
    CURLOPT_TIMEOUT        => 30,
]);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlErr  = curl_error($ch);
curl_close($ch);

if ($response === false || $httpCode !== 200) {
    // Gemini'nin dondurdugu gercek hata mesajini ayikla (tani icin)
    $apiErr = '';
    $errData = json_decode((string) $response, true);
    if (isset($errData['error']['message'])) {
        $apiErr = $errData['error']['message'];
    } else {
        $apiErr = substr((string) $response, 0, 300);
    }
    echo json_encode([
        'success' => false,
        'error'   => 'Gemini API hatasi (HTTP ' . $httpCode . '). ' . $curlErr . ' | ' . $apiErr,
    ]);
    exit;
}

// 4) Gemini cevabindan tarif JSON'unu ayikla
$data = json_decode($response, true);
$text = $data['candidates'][0]['content']['parts'][0]['text'] ?? '';
$recipe = json_decode($text, true);

if (!$recipe || !isset($recipe['title'])) {
    echo json_encode(['success' => false, 'error' => 'Tarif cozumlenemedi.', 'raw' => $text]);
    exit;
}

echo json_encode(['success' => true, 'recipe' => $recipe]);
