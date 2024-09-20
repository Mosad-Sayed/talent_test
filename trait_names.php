<?php
session_start();

// الاتصال بقاعدة البيانات
$mysqli = new mysqli("fdb28.awardspace.net", "4519643_request", "Mosad@55555", "4519643_request");
$mysqli->set_charset("utf8mb4");

if ($mysqli->connect_error) {
    die("فشل الاتصال بقاعدة البيانات: " . $mysqli->connect_error);
}

// استرجاع user_id من الجلسة
$user_id = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : null;

// التحقق من وجود user_id
if (!$user_id) {
    die("لم يتم تحديد معرف المستخدم.");
}

// استعلام لجلب بيانات السمات للمستخدم
$query = "
    SELECT t.id, SUBSTRING_INDEX(t.trait_name, '<br>', 1) AS basic_name, ut.points, ut.classification
    FROM user_traits ut
    JOIN traits t ON ut.trait_id = t.id
    WHERE ut.user_id = ?
";
$stmt = $mysqli->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$traits = [];
$total_points = 0;
while ($row = $result->fetch_assoc()) {
    $traits[] = $row;
    $total_points += $row['points'];
}

$stmt->close();
$mysqli->close();
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تفاصيل السمات</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Tajawal', sans-serif;
            background-color: #f8f9fa;
        }
        .container {
            margin-top: 50px;
        }
        .table thead th {
            background-color: #007bff;
            color: white;
        }
        .table tbody tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        .table tbody tr:hover {
            background-color: #d1ecf1;
        }
        .total-points {
            margin-top: 20px;
            font-weight: bold;
        }
    </style>
</head>
<body>

<div class="container">
    <h1>تفاصيل السمات</h1>
    <table class="table table-striped table-bordered">
        <thead>
            <tr>
                <th>اسم السمة</th>
                <th>النقاط</th>
                <th>التصنيف</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($traits)): ?>
                <?php foreach ($traits as $trait): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($trait['basic_name']); ?></td>
                        <td><?php echo htmlspecialchars($trait['points']); ?></td>
                        <td><?php echo htmlspecialchars($trait['classification']); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="3" class="text-center">لا توجد بيانات لعرضها.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
    <div class="total-points">
        إجمالي النقاط: <?php echo $total_points; ?>
    </div>
</div>

</body>
</html>
