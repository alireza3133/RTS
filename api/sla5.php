<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>داشبورد پردازش سفارش‌ها</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@100;300;400;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        :root {
            --bg-color: #f4f7f6;
            --card-bg: #ffffff;
            --text-color: #333;
            --text-secondary: #777;
            --border-color: #e0e0e0;
            --header-bg: #34495e;
            --header-text: #fff;
            --row-hover: #eef8ff;
            --row-even: #f9f9f9;
            --shadow: 0 8px 25px rgba(0,0,0,0.08);
        }

        /* تنظیمات حالت شب */
        body[data-theme="dark"] {
            --bg-color: #1a1a2e;
            --card-bg: #16213e;
            --text-color: #e0e0e0;
            --text-secondary: #a0a0a0;
            --border-color: #2c3e50;
            --header-bg: #0f3460;
            --header-text: #e0e0e0;
            --row-hover: #1f4068;
            --row-even: #1a2642;
            --shadow: 0 8px 25px rgba(0,0,0,0.4);
        }

        body {
            font-family: 'Vazirmatn', sans-serif; 
            background: var(--bg-color); 
            color: var(--text-color);
            padding: 20px; 
            min-height: 100vh; 
            box-sizing: border-box;
            transition: background 0.3s ease;
            margin: 0;
        }

        /* هدر و کنترل‌ها */
        .header-controls {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            flex-wrap: wrap;
            gap: 15px;
        }

        h2 { margin: 0; color: var(--text-color); display: flex; align-items: center; gap: 10px; }

        .controls-right {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        /* جستجو */
        .search-box {
            position: relative;
        }
        .search-box input {
            padding: 10px 15px;
            border-radius: 20px;
            border: 1px solid var(--border-color);
            background: var(--card-bg);
            color: var(--text-color);
            font-family: inherit;
            width: 250px;
            padding-left: 35px;
            outline: none;
            transition: all 0.3s;
        }
        .search-box input:focus { border-color: #3498db; box-shadow: 0 0 8px rgba(52, 152, 219, 0.3); }
        .search-box i {
            position: absolute; left: 15px; top: 50%; transform: translateY(-50%); color: var(--text-secondary);
        }

        /* دکمه تم */
        .theme-toggle {
            background: var(--header-bg);
            color: #fff;
            border: none;
            width: 40px; height: 40px;
            border-radius: 50%;
            cursor: pointer;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.2em;
            transition: transform 0.2s;
        }
        .theme-toggle:hover { transform: scale(1.1); }

        /* تایمر آپدیت */
        .timer-bar-container {
            position: fixed; top: 0; left: 0; width: 100%; height: 4px; z-index: 9999;
        }
        .timer-bar {
            height: 100%; background: linear-gradient(90deg, #3498db, #2ecc71); width: 0%; transition: width 0.1s linear;
        }

        /* جدول */
        .table-container {
            width: 100%; border-radius: 12px; overflow: hidden;
            box-shadow: var(--shadow);
            background-color: var(--card-bg); 
            border: 1px solid var(--border-color);
            transition: background 0.3s ease;
        }
        table { width: 100%; border-collapse: collapse; }
        th, td {
            padding: 16px 20px; text-align: right;
            border-bottom: 1px solid var(--border-color);
        }
        th {
            background-color: var(--header-bg); color: var(--header-text);
            font-weight: 700; font-size: 0.95em;
        }
        tr:last-child td { border-bottom: 0; }
        
        tbody tr {
            animation: fadeIn 0.5s ease-out;
            background-color: var(--card-bg);
            color: var(--text-color);
        }
        
        tbody tr:nth-child(even) { background-color: var(--row-even); }
        tbody tr:hover { background-color: var(--row-hover); }

        /* انیمیشن ورود ردیف‌ها */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* وضعیت‌ها */
        tr.processing { border-right: 6px solid #3498db; }
        tr.overdue { border-right: 6px solid #e74c3c; }
        tr.completed { border-right: 6px solid #2ecc71; opacity: 0.8; }

        /* سایر استایل‌های محتوا */
        .customer-name { font-weight: 700; font-size: 1.05em; }
        .item-count { font-weight: 800; color: #e67e22; }
        
        .progress-bar-container {
            height: 10px; background-color: var(--border-color); border-radius: 5px; overflow: hidden;
        }
        .progress-bar { height: 100%; border-radius: 5px; transition: width 0.6s cubic-bezier(0.4, 0, 0.2, 1); }
        tr.processing .progress-bar { background: linear-gradient(90deg, #3498db, #5dade2); }
        tr.overdue .progress-bar { background: linear-gradient(90deg, #c0392b, #e74c3c); }
        tr.completed .progress-bar { background: linear-gradient(90deg, #27ae60, #2ecc71); }

        td.info-card { padding: 50px; text-align: center; color: var(--text-secondary); }

        /* موبایل */
        @media (max-width: 768px) {
            .header-controls { flex-direction: column; align-items: stretch; }
            .search-box input { width: 100%; }
            th, td { padding: 10px; font-size: 0.9em; }
            .table-container { overflow-x: auto; }
        }
    </style>
</head>
<body>

    <div class="timer-bar-container">
        <div class="timer-bar" id="timer-bar"></div>
    </div>

    <div class="header-controls">
        <h2><i class="fas fa-chart-line" style="color:#3498db;"></i> داشبورد پردازش سفارش‌ها</h2>
        
        <div class="controls-right">
            <div class="search-box">
                <input type="text" id="searchInput" placeholder="جستجو نام سلر..." onkeyup="filterTable()">
                <i class="fas fa-search"></i>
            </div>
            <button class="theme-toggle" onclick="toggleTheme()" title="تغییر پوسته">
                <i class="fas fa-moon"></i>
            </button>
        </div>
    </div>

    <p style="text-align: center; color: var(--text-secondary); font-size: 0.85em; margin-top: -10px;">
        آخرین بروزرسانی: <span id="update-time-span">...</span>
    </p>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>نام سلر</th>
                    <th>تعداد آیتم</th>
                    <th>وضعیت</th>
                    <th>زمان گذشته / کل</th>
                    <th>میزان پیشرفت</th>
                    <th>زمان تحویل</th>
                </tr>
            </thead>
            <tbody id="dashboard-tbody">
                <?php include 'get_data.php'; ?>
            </tbody>
        </table>
    </div>

<script>
    const dataUrl = 'get_data.php'; 
    const updateInterval = 60000; // 60 ثانیه
    let timeLeft = 0;
    let timerInterval;

    const tableBody = document.getElementById('dashboard-tbody');
    const updateTimeSpan = document.getElementById('update-time-span');
    const timerBar = document.getElementById('timer-bar');
    const searchInput = document.getElementById('searchInput');

    // تنظیم زمان اولیه
    updateTimeSpan.innerText = new Date().toLocaleTimeString('fa-IR');

    function toggleTheme() {
        const body = document.body;
        const btnIcon = document.querySelector('.theme-toggle i');
        
        if (body.getAttribute('data-theme') === 'dark') {
            body.removeAttribute('data-theme');
            btnIcon.classList.replace('fa-sun', 'fa-moon');
            localStorage.setItem('theme', 'light');
        } else {
            body.setAttribute('data-theme', 'dark');
            btnIcon.classList.replace('fa-moon', 'fa-sun');
            localStorage.setItem('theme', 'dark');
        }
    }

    if (localStorage.getItem('theme') === 'dark') {
        toggleTheme();
    }

    function filterTable() {
        const filter = searchInput.value.toLowerCase();
        const rows = tableBody.getElementsByTagName('tr');

        for (let i = 0; i < rows.length; i++) {
            const nameColumn = rows[i].getElementsByTagName('td')[0];
            if (nameColumn && !nameColumn.classList.contains('info-card')) {
                const txtValue = nameColumn.textContent || nameColumn.innerText;
                if (txtValue.toLowerCase().indexOf(filter) > -1) {
                    rows[i].style.display = "";
                } else {
                    rows[i].style.display = "none";
                }
            }
        }
    }

    function startTimer() {
        timeLeft = 0;
        clearInterval(timerInterval);
        timerInterval = setInterval(() => {
            timeLeft += 100;
            const percentage = (timeLeft / updateInterval) * 100;
            timerBar.style.width = percentage + '%';
            
            if (timeLeft >= updateInterval) {
                timeLeft = 0;
                timerBar.style.width = '0%';
                fetchData(); // دریافت مجدد اطلاعات
            }
        }, 100);
    }

    async function fetchData() {
        try {
            const response = await fetch(dataUrl + '?t=' + new Date().getTime());
            if (!response.ok) throw new Error('Network error');
            const htmlData = await response.text();
            
            tableBody.innerHTML = htmlData;
            
            if(searchInput.value !== "") filterTable();
            updateTimeSpan.innerText = new Date().toLocaleTimeString('fa-IR');

        } catch (error) {
            console.error(error);
        }
    }

    // شروع تایمر
    startTimer();

</script>
</body>
</html>
