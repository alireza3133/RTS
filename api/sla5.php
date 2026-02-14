<?php
// tts.php
// جلوگیری از نمایش خطاها در خروجی که فایل صوتی را خراب می‌کند
error_reporting(0);
ini_set('display_errors', 0);

// شروع بافرینگ خروجی (حیاتی برای هاست‌های رایگان)
if (ob_get_level()) ob_end_clean();
ob_start();

header("Access-Control-Allow-Origin: *");

if (!isset($_GET['text']) || empty($_GET['text'])) {
    http_response_code(400);
    die("Error: No text provided.");
}

$text = trim($_GET['text']);
$encodedText = urlencode($text);

// استفاده از API جایگزین گوگل که کمتر مسدود می‌شود
$url = "https://translate.google.com/translate_tts?ie=UTF-8&q={$encodedText}&tl=fa&client=tw-ob&ttsspeed=1";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // غیرفعال کردن بررسی SSL (برای هاست‌های قدیمی)
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 15);

// هدرهای شبیه‌سازی مرورگر (بسیار مهم)
$headers = [
    "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36",
    "Referer: https://translate.google.com/",
    "Accept: audio/mpeg, audio/x-mpeg-3, audio/x-mpeg, audio/x-wav, */*"
];
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

$audioData = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

// پاک کردن هر چیزی که قبل از فایل صوتی در بافر گیر کرده (مثل تبلیغات هاست)
ob_clean(); 

if ($httpCode != 200 || empty($audioData)) {
    http_response_code(500);
    // اگر خطا داد، خطا را چاپ کن تا در کنسول ببینیم
    echo "Google/Curl Error: Code $httpCode - $curlError";
} else {
    // ارسال هدرهای فایل صوتی
    header('Content-Description: File Transfer');
    header('Content-Type: audio/mpeg');
    header('Content-Disposition: inline; filename="tts.mp3"');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . strlen($audioData));
    
    // چاپ فایل صوتی
    echo $audioData;
}

// پایان بافر و ارسال نهایی
ob_end_flush();
exit;
?>