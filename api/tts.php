<?php
// tts.php
// تبدیل متن به گفتار با استفاده از گوگل (بدون نیاز به API Key)

if (!isset($_GET['text'])) {
    header("HTTP/1.1 400 Bad Request");
    die("متنی وارد نشده است");
}

$text = $_GET['text'];

// تنظیمات مهم: اگر سرور ایران است، پروکسی را اینجا وارد کنید
// مثال: $proxy = '127.0.0.1:10809';
$proxy = ''; 

// انکود کردن متن برای ارسال به گوگل
$encodedText = urlencode($text);

// آدرس سرویس TTS گوگل
$url = "https://translate.google.com/translate_tts?ie=UTF-8&q={$encodedText}&tl=fa&client=tw-ob";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
// جعل هویت مرورگر برای جلوگیری از مسدود شدن توسط گوگل
curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/91.0.4472.124 Safari/537.36");
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);

if (!empty($proxy)) {
    curl_setopt($ch, CURLOPT_PROXY, $proxy);
    // اگر پروکسی ساکس است خط زیر را از کامنت خارج کنید
    // curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5);
}

$audioData = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

if (curl_errno($ch)) {
    http_response_code(500);
    echo 'خطای Curl: ' . curl_error($ch);
} elseif ($httpCode != 200) {
    http_response_code($httpCode);
    echo 'خطای گوگل (کد ' . $httpCode . ') - دسترسی سرور به گوگل را چک کنید.';
} else {
    // تنظیم هدر برای پخش فایل صوتی
    header('Content-Type: audio/mpeg');
    header('Content-Length: ' . strlen($audioData));
    echo $audioData;
}

curl_close($ch);
?>