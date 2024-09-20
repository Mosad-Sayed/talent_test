<?php
session_start();

// الاتصال بقاعدة البيانات
$mysqli = new mysqli("fdb28.awardspace.net", "4519643_request", "Mosad@55555", "4519643_request");
$mysqli->set_charset("utf8mb4");

if ($mysqli->connect_error) {
    die("فشل الاتصال بقاعدة البيانات: " . $mysqli->connect_error);
}

// استرجاع بيانات الجلسة
$user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : null;
$answers = isset($_POST['answers']) ? $_POST['answers'] : [];

if (!$user_id) {
    die("لم يتم تحديد معرف المستخدم.");
}

if (empty($answers)) {
    die("لم يتم تقديم أي إجابات.");
}

// تحقق من وجود المستخدم
$result = $mysqli->query("SELECT COUNT(*) AS count FROM users WHERE id = $user_id");
if ($result->fetch_assoc()['count'] == 0) {
    die("المستخدم غير موجود.");
}

// جلب اسم الطالب ورقم الطالب من جدول users
$user_info = $mysqli->query("SELECT name, student_code FROM users WHERE id = $user_id")->fetch_assoc();
if (!$user_info) {
    die("لا يمكن العثور على معلومات الطالب.");
}

$student_name = htmlspecialchars($user_info['name']);
$student_code = htmlspecialchars($user_info['student_code']);

// جلب السمات من جدول user_traits بناءً على user_id
$trait_names = [];
$trait_ids = [];
$stmt = $mysqli->prepare("SELECT trait_id, trait_name FROM user_traits WHERE user_id = ?");
if ($stmt === FALSE) {
    die("فشل إعداد الاستعلام: " . $mysqli->error);
}
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

while ($trait = $result->fetch_assoc()) {
    $trait_ids[] = $trait['trait_id'];
    $trait_names[$trait['trait_id']] = $trait['trait_name'];
}

$stmt->close();

// تحقق من وجود السمات للمستخدم
if (empty($trait_names)) {
    die("لا توجد سمات مرتبطة بالمستخدم.");
}

// حذف النتائج القديمة إذا كانت موجودة للمستخدم
if ($mysqli->query("DELETE FROM user_results WHERE user_id = $user_id") === FALSE) {
    die("فشل حذف النتائج السابقة: " . $mysqli->error);
}

// حساب إجمالي النقاط وتصنيف المستخدم
$total_scores = [];
$question_count = count($answers[0]); // افترض أن جميع السمات تحتوي على نفس عدد الأسئلة
$max_score = $question_count * 5; // بافتراض أن كل سؤال يمكن أن يحصل على نقاط بين 1 و 5

foreach ($answers as $trait_index => $trait_answers) {
    if (!isset($trait_ids[$trait_index])) {
        die("معرف سمة غير صالح.");
    }
    
    $trait_id = $trait_ids[$trait_index];
    $total_score = 0;
    foreach ($trait_answers as $answer) {
        $total_score += intval($answer);
    }

    // تصنيف السمة بناءً على إجمالي النقاط
    $classification = "";
    if ($total_score > 0.8 * $max_score) { // أكثر من 80% من النقاط
        $classification = "قوة";
    } elseif ($total_score > 0.6 * $max_score) { // بين 60% و 80%
        $classification = "موهبة";
    } elseif ($total_score > 0.4 * $max_score) { // بين 40% و 60%
        $classification = "مهارة";
    } else { // أقل من 40%
        $classification = "لا شيء";
    }

    $total_scores[$trait_id] = ['score' => $total_score, 'classification' => $classification];
}

// حفظ السمات وإجمالي النقاط في قاعدة البيانات
$stmt = $mysqli->prepare("INSERT INTO user_results (user_id, trait_id, trait_name, total_score, classification) VALUES (?, ?, ?, ?, ?)");
if ($stmt === FALSE) {
    die("فشل إعداد الاستعلام: " . $mysqli->error);
}

foreach ($total_scores as $trait_id => $data) {
    $trait_name = $trait_names[$trait_id];
    $stmt->bind_param("iisis", $user_id, $trait_id, $trait_name, $data['score'], $data['classification']);
    if ($stmt->execute() === FALSE) {
        die("فشل إدخال النتائج: " . $stmt->error);
    }
}

$stmt->close();

// جلب النتائج المحفوظة من user_results
$results = $mysqli->query("SELECT trait_name, total_score, classification FROM user_results WHERE user_id = $user_id");

$mysqli->close();
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>نتائج المستخدم</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #a8c0ff, #a8e0a4);
            font-family: 'Roboto', sans-serif;
        }
        .container {
            max-width: 900px;
            margin-top: 50px;
        }
        .card {
            border-radius: 15px;
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.1);
            background-color: #ffffff;
        }
        table {
            background: #ffffff;
            border-radius: 10px;
            overflow: hidden;
        }
        th, td {
            text-align: center;
            vertical-align: middle;
            padding: 15px;
        }
        .table thead th {
            background-color: #007bff;
            color: white;
            font-weight: 700;
        }
        .table tbody tr:nth-child(odd) {
            background-color: #f9f9f9;
        }
        .table tbody tr:hover {
            background-color: #e2e6ea;
        }
        .table tbody td {
            border: 1px solid #dee2e6;
        }
        .btn {
            border-radius: 50px;
            font-weight: 600;
            margin: 0 10px;
        }
        .btn-print {
            background-color: #28a745;
            color: white;
        }
        .btn-print:hover {
            background-color: #218838;
        }
        .btn-logout {
            background-color: #dc3545;
            color: white;
        }
        .btn-logout:hover {
            background-color: #c82333;
        }
        @media print {
            .btn {
                display: none;
            }
            .container {
                max-width: 100%;
            }
            table {
                width: 100%;
            }
                
        }
        .student-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }
            .alert {
    background-color: #d1ecf1; /* لون الخلفية */
    color: #0c5460; /* لون النص */
    padding: 20px; /* تباعد داخلي */
    border-radius: 10px; /* زوايا مدورة */
    margin-top: 20px; /* تباعد أعلى */
    border: 1px solid #bee5eb; /* حدود */
    direction: rtl; /* اتجاه النص من اليمين لليسار */
    text-align: right; /* محاذاة النص لليمين */
}
.alert h5 {
    font-weight: 700; /* خط عريض للعناوين */
    margin-bottom: 10px; /* تباعد أسفل العنوان */
}
.alert p {
    margin: 5px 0; /* تباعد بين الفقرات */
}
    </style>
</head>
<body>

<div class="container">
    <div class="card">
        <div class="card-header text-center">
            <h3>نتيجة الأختبار</h3>
        </div>
       <div class="card-body">
    <div class="student-info">
        <div>
            <h4>اسم الطالب: <?php echo $student_name; ?></h4>
        </div>
        <div>
            <h4>رقم الطالب: <?php echo $student_code; ?></h4>
        </div>
    </div>
    <?php if ($results->num_rows > 0): ?>
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>اسم السمة</th>
                <th>إجمالي النقاط</th>
                <th>التصنيف</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $results->fetch_assoc()): ?>
            <tr>
                <td><?php echo htmlspecialchars($row['trait_name']); ?></td>
                <td><?php echo htmlspecialchars($row['total_score']); ?></td>
                <td style="color: 
                    <?php 
                        if ($row['classification'] == 'قوة') {
                            echo '#28a745';
                        } elseif ($row['classification'] == 'موهبة') {
                            echo '#28a745';
                        } elseif ($row['classification'] == 'مهارة') {
                            echo '#17a2b8';
                        } else {
                            echo '#dc3545';
                        }
                    ?>;
                    font-weight: bold;
                    font-size: 1.0em;
                ">
                <?php echo htmlspecialchars($row['classification']); ?>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
    <?php else: ?>
    <p class="text-center">لا توجد نتائج متاحة لهذا المستخدم.</p>
    <?php endif; ?>

    <!-- نص إضافي موجه للمتدرب -->
    <div class="alert alert-info mt-4">
        <h5>ملاحظة مهمة:</h5>
        <p>
            اعلم عزيزي المتدرب أن هذه النقاط تمثل قياسًا لمجموعة من جوانب القوة والمهارات التي تمتلكها. كل درجة تعكس مستوى توافقك مع المهارات المطلوبة، وهي بمثابة مرشد لك لفهم نقاط القوة التي تحتاج لتعزيزها والمجالات التي تتطلب المزيد من التطوير.
        </p>
        <p>
            <strong></strong> إذا حصلت على هذا التقييم، فهذا يعني أنك بحاجة إلى مراجعة بعض الأساسيات والعمل بشكل أكبر على تحسين هذه المهارة.
        </p>
        <p>
            اجلس مع كوتش: من المهم أن تتحدث مع مدربك أو مستشارك لمناقشة النتائج ووضع خطة تطويرية تساعدك على تعزيز نقاط الضعف والاستفادة من نقاط القوة.
        </p>
        <p>
            تذكر أن التقييم هو خطوة في رحلتك نحو النجاح، وليس هدفًا نهائيًا. استفد من هذه الفرصة لتطوير نفسك والوصول إلى أفضل نسخة منك.
        </p>
    </div>
</div>
        </div>
        <div class="card-footer text-center">
    <a href="javascript:window.print()" class="btn btn-print">
        <i class="fas fa-print"></i> طباعة
    </a>
    <a href="index.php" class="btn btn-logout">
        <i class="fas fa-sign-out-alt"></i> خروج
    </a>
    <form action="send_email.php" method="POST" style="display:inline;">
        <input type="hidden" name="user_id" value="<?php echo $user_id; ?>">
        <button type="submit" class="btn btn-email">
            <i class="fas fa-envelope"></i> إرسال عبر البريد الإلكتروني
        </button>
    </form>
</div>
    </div>
</div>

</body>
</html>
