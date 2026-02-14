<?php
// تنظیمات حیاتی
mb_internal_encoding("UTF-8");
date_default_timezone_set('Asia/Tehran');

// -----------------------------------------------------------------
// URL اسکریپت گوگل شیت
$csvUrl = "https://script.google.com/macros/s/AKfycbyP6W1EkPb83IHQIpJ0zNLahF3t5EhJD5f3bx9KU8SX59RIqyFcSXzRqIdV2haHfU0noA/exec";
// -----------------------------------------------------------------

// *** خواندن تنظیمات از فایل JSON ***
$configFile = 'config.json';
$configData = @file_get_contents($configFile);
if ($configData) {
    $config = json_decode($configData, true);
    $baseTimeSeconds = intval($config['base_minutes']) * 60; 
    $secondsPerItem = intval($config['seconds_per_item']);
} else {
    $baseTimeSeconds = 600; 
    $secondsPerItem = 10;
}

define('COMPLETED_GRACE_PERIOD', 3600); 

function formatDuration($seconds) {
    $minutes = floor($seconds / 60);
    if ($minutes < 1) return "کمتر از ۱ دقیقه";
    $hours = floor($minutes / 60);
    $mins = $minutes % 60;
    if ($hours > 0) return $hours . " ساعت و " . $mins . " دقیقه";
    return $minutes . " دقیقه";
}

$allOrders = [];
$now = time(); 

$csvData = @file_get_contents($csvUrl);

if ($csvData === FALSE || empty($csvData)) {
    $showError = "خطا: عدم ارتباط با گوگل شیت. لطفاً چند لحظه صبر کنید.";
} else {
    $showError = false;
    
    $bom = "\xEF\xBB\xBF";
    if (strncmp($csvData, $bom, 3) === 0) $csvData = substr($csvData, 3);
    if (!mb_check_encoding($csvData, 'UTF-8')) $csvData = mb_convert_encoding($csvData, 'UTF-8', 'auto');

    $lines = explode("\r\n", $csvData); 
    $header = str_getcsv(array_shift($lines)); 

    foreach ($lines as $line) {
        if (empty(trim($line))) continue; 
        $row = str_getcsv($line);
        if (count($row) < 24) continue; 

        $dateChristian = $row[23] ?? ''; 
        $startTimeStr = $row[4] ?? '';  
        $endTimeStr = $row[18] ?? ''; 
        $customer = $row[8] ?? ''; 
        $itemCount = (int)($row[17] ?? 0); 

        $fullStartTimeStr = $dateChristian . ' ' . $startTimeStr;
        $startTime = strtotime($fullStartTimeStr);
        
        if ($startTime === false) continue; 

        $allowedSeconds = $baseTimeSeconds + ($itemCount * $secondsPerItem);
        $deadlineTimestamp = $startTime + $allowedSeconds;

        $isCompleted = !empty(trim($endTimeStr));
        $endTimestamp = 0;

        if ($isCompleted) {
            $endTimestamp = strtotime($dateChristian . ' ' . $endTimeStr);
            if ($endTimestamp === false) continue;

            if (($now - $endTimestamp) > COMPLETED_GRACE_PERIOD) continue; 

            $elapsedSeconds = $endTimestamp - $startTime;
            $status_class = 'completed';
            $status_text = 'تکمیل شده';
            $icon = 'fas fa-check-circle';
            $time_label = 'مدت پردازش:';
            $time_value_human = formatDuration($elapsedSeconds);
            $goal_time_label = 'زمان تحویل:';
            $goal_time_value = date('H:i', $endTimestamp);
            
            $sortPriority = 3; 
            $progressPercent = 100;

        } else {
            if ($itemCount <= 0) continue;

            $elapsedSeconds = $now - $startTime;
            $goal_time_label = 'موعد تحویل:';
            $goal_time_value = date('H:i:s', $deadlineTimestamp);
            
            if ($allowedSeconds > 0) {
                $progressPercent = ($elapsedSeconds / $allowedSeconds) * 100;
            } else {
                $progressPercent = 0;
            }
            $displayPercent = min(100, $progressPercent);
            $time_label = 'زمان گذشته:';
            $time_value_human = formatDuration($elapsedSeconds);
            
            if ($now > $deadlineTimestamp) {
                $status_class = 'overdue';
                $status_text = 'تاخیر دارد';
                $icon = 'fas fa-exclamation-triangle';
                $sortPriority = 1;
                $displayPercent = 100;
            } else {
                $status_class = 'processing';
                $status_text = 'در حال پردازش';
                $icon = 'fas fa-cog fa-spin'; 
                $sortPriority = 2;
            }
            $progressPercent = $displayPercent;
        }

        // ایجاد شناسه یکتا (نام + زمان شروع)
        // این باعث می شود سفارش جدید همان مشتری، یک سفارش متفاوت در نظر گرفته شود
        $uniqueID = md5($customer . $startTime);

        $allOrders[] = [
            'unique_id' => $uniqueID, // <--- اضافه شده
            'customer' => $customer,
            'items' => $itemCount,
            'start_timestamp' => $startTime,
            'end_timestamp' => $endTimestamp, 
            'time_label' => $time_label,
            'time_value_human' => $time_value_human,
            'goal_time_label' => $goal_time_label,
            'goal_time_value' => $goal_time_value,
            'status_class' => $status_class, 
            'status_text' => $status_text,
            'icon' => $icon,
            'sort_priority' => $sortPriority,
            'progress_percent' => round($progressPercent) 
        ];
    }
} 

if (!$showError) {
    usort($allOrders, function($a, $b) {
        if ($a['sort_priority'] != $b['sort_priority']) {
            return $a['sort_priority'] <=> $b['sort_priority']; 
        }
        if ($a['sort_priority'] == 3) {
            return $b['end_timestamp'] <=> $a['end_timestamp'];
        }
        return $a['start_timestamp'] <=> $b['start_timestamp'];
    });
}

if ($showError): ?>
    <tr>
        <td colspan="6" class="info-card error">
            <i class="fas fa-wifi-slash" style="color: #e74c3c;"></i>
            <?php echo $showError; ?>
        </td>
    </tr>
<?php elseif (count($allOrders) === 0): ?>
    <tr>
        <td colspan="6" class="info-card">
            <i class="fas fa-clipboard-check" style="color: #2ecc71;"></i>
            سفارش فعالی وجود ندارد.
        </td>
    </tr>
<?php else: ?>
    <?php foreach ($allOrders as $order): ?>
        <tr class="<?php echo $order['status_class']; ?>" data-uid="<?php echo $order['unique_id']; ?>">
            <td class="customer-name"><?php echo htmlspecialchars($order['customer']); ?></td>
            <td class="item-count"><?php echo htmlspecialchars($order['items']); ?></td>
            <td><i class="icon <?php echo $order['icon']; ?>"></i> <?php echo $order['status_text']; ?></td>
            <td class="time-value"><?php echo $order['time_value_human']; ?></td>
            <td>
                <div class="progress-bar-container">
                    <div class="progress-bar" style="width: <?php echo $order['progress_percent']; ?>%;"></div>
                </div>
            </td>
            <td class="time-value">
                <strong><?php echo $order['goal_time_label']; ?></strong>
                <?php echo $order['goal_time_value']; ?>
            </td>
        </tr>
    <?php endforeach; ?>
<?php endif; ?>