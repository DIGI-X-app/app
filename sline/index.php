<?php
set_time_limit(0);

date_default_timezone_set('Asia/Tehran');

// تابع نمایش پیام‌ها از فایل
function displayMessages() {
    $messages = file_get_contents('chat.json');
    echo nl2br($messages);
}

// تشخیص زبان متن
function detectLanguage($text) {
    // برای تشخیص فارسی بودن یک رشته می‌توان از متد‌های مختلف استفاده کرد. به عنوان مثال می‌توانید از اعداد فارسی و یا کاراکترهای خاص فارسی استفاده کنید.
    if (preg_match('/[\x{0600}-\x{06FF}]/u', $text)) {
        return 'fa'; // زبان فارسی
    } else {
        return 'en'; // زبان انگلیسی
    }
}

// تابع ذخیره اطلاعات کاربر در فایل JSON
function saveUserInfo($data) {
    $file = 'ip.json';
    $jsonData = file_exists($file) ? file_get_contents($file) : '[]';
    $existingData = json_decode($jsonData, true);
    $existingData[] = $data;
    $jsonData = json_encode($existingData, JSON_PRETTY_PRINT);
    file_put_contents($file, $jsonData);
}

if(isset($_POST['submit'])) {
    $message = $_POST['message'];
    $username = $_POST['username'];
    $time = date('H:i'); // زمان به فرمت ساعت:دقیقه
    
    $language = detectLanguage($username);
    
    if ($language != 'fa') {
        die("نام کاربری باید به فارسی باشد.");
    }
    
    // بررسی زبان پیام و افزودن زمان و نام کاربر در جایگاه مناسب
    if (detectLanguage($message) == 'fa') {
        $result = file_put_contents('1.docx', "$username: $message - $time <br>", FILE_APPEND);
    } else {
        $result = file_put_contents('1.docx', "$time - $username: $message <br>", FILE_APPEND);
    }
    
    if($result === false) {
        error_log("Unable to write to 1.docx file.");
    }
    
    // ذخیره اطلاعات کاربر
    $userInfo = array(
        'username' => $username, // نام کاربر
        'device_model' => $_SERVER['HTTP_USER_AGENT'], // مدل دستگاه
        'ip_address' => $_SERVER['REMOTE_ADDR'], // آی‌پی کاربر
        'login_time' => date('Y-m-d H:i:s') // زمان ورود
    );
    saveUserInfo($userInfo);
}
?>
<!DOCTYPE html>
<html lang="fa">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SLine</title>
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f0f0f0;
            color: #333;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        h2 {
            color: #007bff;
            text-align: right;
        }
        form {
            margin-bottom: 20px;
            text-align: right;
        }
        textarea, input[type="text"] {
            width: calc(100% - 20px);
            padding: 10px;
            margin: 5px 0;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
            resize: none;
            direction: rtl;
        }
        textarea {
            height: 100px;
        }
        input[type="submit"] {
            background-color: #007bff;
            color: #fff;
            border: none;
            border-radius: 4px;
            padding: 10px 20px;
            cursor: pointer;
        }
        hr {
            border: 0;
            border-top: 1px solid #ccc;
            margin: 20px 0;
        }
        .message {
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 10px;
        }
        .message.en {
            background-color: #f2f2f2;
            text-align: left;
        }
        .message.fa {
            background-color: #e6f7ff;
            text-align: right;
        }
    </style>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script>
        function updateMessages() {
            $.ajax({
                url: "1.docx",
                cache: false,
                success: function(data) {
                    $("#messages").html(data);
                }
            });
        }
        
        $(document).ready(function() {
            setInterval(updateMessages, 3000);
        });
    </script>
</head>
<body>
    <div class="container">
        <h2 style="text-align: center;">SLine</h2>
        <div id="messages">
            <?php displayMessages(); ?>
        </div>
        <hr>
        <form method="post" action="">
            <input type="text" name="username" placeholder="نام خود را به فارسی وارد کنید" required><br>
            <textarea name="message" rows="4" placeholder="پیام" required></textarea><br>
            <input type="submit" name="submit" value="ارسال پیام">
        </form>
        <img src="1.jpg" alt="GIF" style="width: 100%; max-width: 400px;">
    </div>
</body>
</html>
