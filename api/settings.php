<?php
// فایل ذخیره تنظیمات
$configFile = 'config.json';
$message = "";

// اگر فرم ارسال شده باشد، تنظیمات را ذخیره کن
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newConfig = [
        'base_minutes' => intval($_POST['base_minutes']),
        'seconds_per_item' => intval($_POST['seconds_per_item'])
    ];
    
    // ذخیره در فایل JSON
    if (file_put_contents($configFile, json_encode($newConfig, JSON_PRETTY_PRINT))) {
        $message = "<div class='success'>تنظیمات با موفقیت ذخیره شد!</div>";
    } else {
        $message = "<div class='error'>خطا در ذخیره تنظیمات. دسترسی فایل را چک کنید.</div>";
    }
}

// خواندن تنظیمات فعلی
$currentConfig = json_decode(file_get_contents($configFile), true);
// مقادیر پیش‌فرض در صورت نبود فایل
if (!$currentConfig) {
    $currentConfig = ['base_minutes' => 10, 'seconds_per_item' => 10];
}
?>

<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تنظیمات داشبورد</title>
    <link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@300;400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { font-family: 'Vazirmatn', sans-serif; background: #f4f7f6; color: #333; padding: 20px; display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0; }
        .card { background: #fff; padding: 40px; border-radius: 15px; box-shadow: 0 10px 25px rgba(0,0,0,0.1); width: 100%; max-width: 400px; }
        h2 { text-align: center; color: #34495e; margin-bottom: 30px; }
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 8px; font-weight: bold; color: #555; }
        input[type="number"] { width: 100%; padding: 12px; border: 2px solid #e0e0e0; border-radius: 8px; font-family: inherit; font-size: 1.1em; box-sizing: border-box; transition: 0.3s; text-align: center; }
        input[type="number"]:focus { border-color: #3498db; outline: none; }
        button { width: 100%; padding: 14px; background: #3498db; color: #fff; border: none; border-radius: 8px; font-size: 1.1em; cursor: pointer; transition: 0.3s; font-family: inherit; font-weight: bold; }
        button:hover { background: #2980b9; }
        .success { background: #d4edda; color: #155724; padding: 15px; border-radius: 8px; margin-bottom: 20px; text-align: center; border: 1px solid #c3e6cb; }
        .error { background: #f8d7da; color: #721c24; padding: 15px; border-radius: 8px; margin-bottom: 20px; text-align: center; border: 1px solid #f5c6cb; }
        .back-link { display: block; text-align: center; margin-top: 20px; color: #777; text-decoration: none; }
        .back-link:hover { color: #333; }
    </style>
</head>
<body>

<div class="card">
    <h2><i class="fas fa-cogs"></i> تنظیمات زمان‌بندی</h2>
    
    <?php echo $message; ?>

    <form method="POST">
        <div class="form-group">
            <label for="base_minutes">زمان پایه (دقیقه):</label>
            <input type="number" id="base_minutes" name="base_minutes" value="<?php echo $currentConfig['base_minutes']; ?>" required min="0">
            <small style="color: #999; display:block; margin-top:5px;">مثلاً ۱۰ دقیقه برای هر سفارش</small>
        </div>

        <div class="form-group">
            <label for="seconds_per_item">زمان اضافه برای هر آیتم (ثانیه):</label>
            <input type="number" id="seconds_per_item" name="seconds_per_item" value="<?php echo $currentConfig['seconds_per_item']; ?>" required min="0">
            <small style="color: #999; display:block; margin-top:5px;">مثلاً ۱۰ ثانیه به ازای هر کالا</small>
        </div>

        <button type="submit">ذخیره تغییرات</button>
    </form>

    <a href="index.html" class="back-link">بازگشت به داشبورد</a>
</div>

</body>
</html>