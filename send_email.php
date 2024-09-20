<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php'; // تأكد من تحميل مكتبة PHPMailer عبر Composer

session_start();

// الاتصال بقاعدة البيانات
$mysqli = new mysqli("fdb28.awardspace.net", "4519643_request", "Mosad@55555", "4519643_request");
$mysqli->set_charset("utf8mb4");

if ($mysqli->connect_error) {
    die("فشل الاتصال بقاعدة البيانات: " . $mysqli->connect_error);
}

// استرجاع بيانات الجلسة
$user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : null;

if (!$user_id) {
    die("لم يتم تحديد معرف المستخدم.");
}

// جلب بيانات الطالب من جدول users
$user_info = $mysqli->query("SELECT email, name, student_code FROM users WHERE id = $user_id")->fetch_assoc();
if (!$user_info) {
    die("لا يمكن العثور على معلومات الطالب.");
}

$email = htmlspecialchars($user_info['email']);
$name = htmlspecialchars($user_info['name']);
$student_code = htmlspecialchars($user_info['student_code']);

if (!$email) {
    die("لا يوجد بريد إلكتروني مسجل لهذا الطالب.");
}

// جلب النتائج المحفوظة من user_results
$results = $mysqli->query("SELECT trait_name, total_score, classification FROM user_results WHERE user_id = $user_id");

$mysqli->close();

// إعداد البريد الإلكتروني
$mail = new PHPMailer(true);
$success = false; // متغير لتعقب حالة الإرسال
$message = ''; // رسالة الحالة
try {
    // إعدادات السيرفر
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'mosadelsayed8@gmail.com';
    $mail->Password = 'vchb yoxr gyul kbsr'; 
    $mail->SMTPSecure = 'tls';
    $mail->Port = 587;

    // المرسل والمستلم
    $mail->setFrom('mosadelsayed8@gmail.com', 'Test Results');
    $mail->addAddress($email);
        
    $mail->CharSet = 'UTF-8'; // تعيين الترميز إلى UTF-8


    // إعداد المحتوى
    $mail->isHTML(true);
    $mail->Subject = 'نتائج الأختبار';
    
    // محتوى البريد الإلكتروني
    $bodyContent = '<h2>نتائج الأختبار</h2>';
    $bodyContent .= '<p>عزيزي الطالب ' . $name . ',</p>';
    $bodyContent .= '<p>نتمنى أن تكون بخير. إليك نتائج اختبارك. كود الطالب الخاص بك هو: ' . $student_code . '.</p>';
    $bodyContent .= '<p>يرجى مراجعة النتائج أدناه:</p>';
    
    if ($results->num_rows > 0) {
        $bodyContent .= '<table border="1" cellpadding="10" cellspacing="0" style="width:100%; border-collapse:collapse; direction: rtl;">';
        $bodyContent .= '<thead><tr><th>اسم السمة</th><th>إجمالي النقاط</th><th>التصنيف</th></tr></thead>';
        $bodyContent .= '<tbody>';
        while ($row = $results->fetch_assoc()) {
            $bodyContent .= '<tr>';
            $bodyContent .= '<td>' . htmlspecialchars($row['trait_name']) . '</td>';
            $bodyContent .= '<td>' . htmlspecialchars($row['total_score']) . '</td>';
            $bodyContent .= '<td>' . htmlspecialchars($row['classification']) . '</td>';
            $bodyContent .= '</tr>';
        }
        $bodyContent .= '</tbody></table>';
    } else {
        $bodyContent .= '<p>لا توجد نتائج متاحة لهذا المستخدم.</p>';
    }
    
    $mail->Body = $bodyContent;
    $mail->send();
    
    $success = true;
    $message = 'تم إرسال النتيجة على البريد الإلكترونى بنجاح';
} catch (Exception $e) {
    $message = 'لم يتم إرسال البريد الإلكتروني. خطأ: ' . $mail->ErrorInfo;
}
?>

<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="refresh" content="5;url=index.php"> <!-- تحويل بعد 5 ثوانٍ -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <title>نتائج الإرسال</title>
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background: linear-gradient(to right, #a8ff78, #78ffd6); /* التدرج الخلفي */
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
			
        }
        .card {
            background-color: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            text-align: center;
            width: 300px;
        }
        .card h2 {
            color: #4CAF50;
        }
        .card p {
            font-size: 16px;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="card">
        <h2><?php echo $success ? 'نجاح!' : 'فشل'; ?></h2>
        <p><?php echo $message; ?></p>
        <p>سيتم الإنتهاء خلال 5 ثوانى .. </p>
    </div>
</body>
</html>
