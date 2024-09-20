<?php
session_start();

// الاتصال بقاعدة البيانات
$mysqli = new mysqli("fdb28.awardspace.net", "4519643_request", "Mosad@55555", "4519643_request");
$mysqli->set_charset("utf8mb4");

if ($mysqli->connect_error) {
    die("فشل الاتصال بقاعدة البيانات: " . $mysqli->connect_error);
}

// استرجاع بيانات الجلسة
$selected_traits = isset($_SESSION['selected_traits']) ? $_SESSION['selected_traits'] : [];
$user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : null;
$final_traits = isset($_POST['final_traits']) ? array_map('intval', $_POST['final_traits']) : [];

// إذا لم يتم تحديد معرف المستخدم، قم بتسجيل رسالة ولكن لا تتوقف
if (!$user_id) {
    error_log("لم يتم تحديد معرف المستخدم.");
}

// تحقق من اختيار السمات
if (empty($final_traits)) {
    error_log("لم يتم اختيار أي سمات.");
    // يمكنك أيضًا إرجاع أو إعطاء قيمة افتراضية هنا إذا أردت
    $final_traits = []; // أو استخدام قيمة افتراضية
}

if (count($final_traits) !== 7) {
    error_log("يجب عليك اختيار 7 سمات.");
    // هنا يمكنك أيضًا تحديد كيفية التعامل مع العدد غير الصحيح
}

// تحقق من وجود المستخدم
$result = $mysqli->query("SELECT COUNT(*) AS count FROM users WHERE id = $user_id");
if ($result) {
    $userExists = $result->fetch_assoc()['count'] > 0;
} else {
    $userExists = false; // في حالة فشل الاستعلام
}

if (!$userExists) {
    error_log("المستخدم غير موجود.");
}

// إذا لم يكن هناك سمات، أخرج من الكود
if (count($final_traits) == 0) {
    $mysqli->close();
    exit; // أو يمكنك إعادة توجيه المستخدم أو إظهار رسالة
}

// جلب أسماء السمات بناءً على الـ ID الخاص بكل سمة
$trait_names = [];
$placeholders = implode(',', array_fill(0, count($final_traits), '?'));
$stmt = $mysqli->prepare("SELECT id, SUBSTRING_INDEX(trait_name, '<br>', 1) AS basic_name FROM traits WHERE id IN ($placeholders)");
if ($stmt === FALSE) {
    die("فشل إعداد الاستعلام: " . $mysqli->error);
}

$types = str_repeat('i', count($final_traits));
$stmt->bind_param($types, ...$final_traits);
$stmt->execute();
$result = $stmt->get_result();

while ($trait = $result->fetch_assoc()) {
    $trait_names[$trait['id']] = $trait['basic_name'];
}

$stmt->close();

if (count($trait_names) != count($final_traits)) {
    error_log("بعض السمات غير موجودة.");
}

// حذف السمات السابقة للمستخدم إذا كان معرف المستخدم موجودًا
if ($userExists) {
    if ($mysqli->query("DELETE FROM user_traits WHERE user_id = $user_id") === FALSE) {
        error_log("فشل حذف السمات السابقة: " . $mysqli->error);
    }

    // إدخال السمات الجديدة للمستخدم
    $stmt = $mysqli->prepare("INSERT INTO user_traits (user_id, trait_id, trait_name) VALUES (?, ?, ?)");
    if ($stmt === FALSE) {
        die("فشل إعداد الاستعلام: " . $mysqli->error);
    }

    foreach ($final_traits as $trait_id) {
        $trait_name = $trait_names[$trait_id];
        $stmt->bind_param("iis", $user_id, $trait_id, $trait_name);
        if ($stmt->execute() === FALSE) {
            error_log("فشل إدخال السمة: " . $stmt->error);
        }
    }

    $stmt->close();
}

$mysqli->close();
?>



<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إجابة الأسئلة</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.21/css/jquery.dataTables.min.css">
    <style>
        body {
    background: linear-gradient(to right, #0056b3, #28a745);
    background-size: cover;
    background-position: center;
    font-family: 'Tajawal', sans-serif;
    color: #fff;
    min-height: 100vh;
    display: flex;
    justify-content: center;
    align-items: center;
}

.container {
    margin-top: 30px;
    max-width: 1400px;
}

.card {
    border: none;
    border-radius: 15px;
    box-shadow: 0 15px 30px rgba(0, 0, 0, 0.2);
    background-color: rgba(255, 255, 255, 0.9);
}

.card-header {
    background: linear-gradient(to right, #0056b3, #28a745);
    border-top-left-radius: 15px;
    border-top-right-radius: 15px;
    padding: 20px;
}

.card-header h3 {
    margin: 0;
    font-size: 1.8rem;
    font-weight: bold;
    color: #fff;
}

.card-body {
    padding: 20px;
}

table.dataTable thead {
    background-color: #0056b3;
    color: white;
}

table.dataTable {
    background-color: #fff;
    color: #333;
}

table.dataTable tbody tr:hover {
    background-color: #f1f1f1;
}

.form-control {
    border-radius: 10px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    width: 100%;
    height: auto;
    padding: 10px;
}

/* الأزرار */
.btn-success {
    margin-top: 20px;
    border-radius: 50px;
    font-size: 1.2rem;
    padding: 10px 30px;
    transition: all 0.3s ease-in-out;
    background-color: #28a745;
    color: #fff;
}

.btn-success:hover {
    background-color: #218838;
    box-shadow: 0 10px 20px rgba(40, 167, 69, 0.4);
}

/* الجداول */
.table-responsive {
    margin-top: 30px;
    border-radius: 15px;
    overflow: hidden;
}

th, td {
    text-align: center;
    vertical-align: middle;
}

.merged-cell {
    text-align: center;
    font-weight: bold;
    background-color: #28a745;
    color: white;
}



table.dataTable td:nth-child(2) {
    background-color: #e1bee7; /* لون خلفية العمود الثاني */
}


table.dataTable td:nth-child(3) {
    background-color: #c5e1a5; /* لون خلفية العمود الثالث */
}

table.dataTable td:nth-child(4) {
    background-color: #ffccbc; /* لون خلفية العمود الرابع */
}
table.dataTable td:nth-child(5) {
    background-color: #e1bee7; /* لون خلفية العمود الثاني */
}
table.dataTable td:nth-child(6) {
    background-color:  #c5e1a5; /* لون خلفية العمود الثاني */
}
table.dataTable td:nth-child(7) {
    background-color: #ffccbc; /* لون خلفية العمود الرابع */
}
table.dataTable td:nth-child(8) {
    background-color:  #c5e1a5; /* لون خلفية العمود الرابع */
}


/* إضافة تنسيق للصفوف */
table.dataTable tbody tr:nth-child(even) {
    background-color: #f9f9f9; /* لون خلفية الصفوف الزوجية */
}

table.dataTable tbody tr:nth-child(odd) {
    background-color: #fff; /* لون خلفية الصفوف الفردية */
}
    </style>
</head>
<body>

<div class="container">
    <div class="card">
        <div class="card-header text-center">
            <h3>إجابة الأسئلة</h3>
        </div>
        <div class="card-body">
            <form action="result.php" method="POST">
                <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($user_id); ?>">
				<h3 style="color:black;text-align:center;    font-family: 'Tajawal', sans-serif;">قيم نفسك من 1 إلى 5 حيث يمثل الرقم 1 'لا أوافق بشدة'، والرقم 5 'أوافق بشدة'. اختر الدرجة التي تعكس مدى توافقك مع العبارة المطروحة، مع العلم أن تقييمك سيساعدنا في فهم وجهة نظرك بشكل أفضل وتعزيز تجربتك</h3>
                <div class="table-responsive">
                    <table id="traitsTable" class="display table table-striped table-bordered">
                        <thead>
                            <!-- الصف الأول: عنوان مميزاتي والسمات -->
                            <tr>
                                <th>مميزاتي</th>
                                <?php foreach ($trait_names as $trait_name): ?>
                                    <th><?php echo htmlspecialchars($trait_name); ?></th>
                                <?php endforeach; ?>
                            </tr>
                            <!-- الصف الثاني: النجاح ودرجاتي (دمج درجاتي) -->
                            <tr>
                                <th style="background-color:blue;color:white;">النجاح</th>
                                <th colspan="<?php echo count($trait_names); ?>" class="merged-cell">درجاتي</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- السؤال الأول: النجاح -->
                            <tr>
    <td>أنا ناجح في أداء هذا العمل</td>
    <?php for ($i = 0; $i < count($trait_names); $i++): ?>
        <td>
            <select name="answers[<?php echo $i; ?>][1]" class="form-control">
                <option value="">-- اختر --</option> <!-- الخيار الفارغ -->
                <option value="1">1 - لا أوافق بشدة</option>
                <option value="2">2 - لا أوافق</option>
                <option value="3">3 - محايد</option>
                <option value="4">4 - أوافق</option>
                <option value="5">5 - أوافق بشدة</option>
            </select>
        </td>
    <?php endfor; ?>
</tr>
                            <!-- السؤال الثاني: النجاح -->
                           <tr>
    <td>أنا ناجح في أداء هذا العمل</td>
    <?php for ($i = 0; $i < count($trait_names); $i++): ?>
        <td>
            <select name="answers[<?php echo $i; ?>][2]" class="form-control">
                <option value="">-- اختر --</option> <!-- الخيار الفارغ -->
                <option value="1">1 - لا أوافق بشدة</option>
                <option value="2">2 - لا أوافق</option>
                <option value="3">3 - محايد</option>
                <option value="4">4 - أوافق</option>
                <option value="5">5 - أوافق بشدة</option>
            </select>
        </td>
    <?php endfor; ?>
</tr>
                            <!-- السؤال الثالث: النجاح -->
                            <tr>
    <td>أنا ناجح في أداء هذا العمل</td>
    <?php for ($i = 0; $i < count($trait_names); $i++): ?>
        <td>
            <select name="answers[<?php echo $i; ?>][3]" class="form-control">
                <option value="">-- اختر --</option> <!-- الخيار الفارغ -->
                <option value="1">1 - لا أوافق بشدة</option>
                <option value="2">2 - لا أوافق</option>
                <option value="3">3 - محايد</option>
                <option value="4">4 - أوافق</option>
                <option value="5">5 - أوافق بشدة</option>
            </select>
        </td>
    <?php endfor; ?>
</tr>
                            <!-- الصف الثالث: الغريزة ودرجاتي (دمج درجاتي) -->
                            <tr>
                                <th style="background-color:blue;color:white;">الغريزة</th>
                                <th colspan="<?php echo count($trait_names); ?>" class="merged-cell">درجاتي</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- السؤال الأول: النجاح -->
                          <tr>
    <td>أنا ناجح في أداء هذا العمل</td>
    <?php for ($i = 0; $i < count($trait_names); $i++): ?>
        <td>
            <select name="answers[<?php echo $i; ?>][1]" class="form-control">
                <option value="">-- اختر --</option> <!-- الخيار الفارغ -->
                <option value="1">1 - لا أوافق بشدة</option>
                <option value="2">2 - لا أوافق</option>
                <option value="3">3 - محايد</option>
                <option value="4">4 - أوافق</option>
                <option value="5">5 - أوافق بشدة</option>
            </select>
        </td>
    <?php endfor; ?>
</tr>
                            <!-- السؤال الثاني: النجاح -->
                           <tr>
    <td>أنا ناجح في أداء هذا العمل</td>
    <?php for ($i = 0; $i < count($trait_names); $i++): ?>
        <td>
            <select name="answers[<?php echo $i; ?>][2]" class="form-control">
                <option value="">-- اختر --</option> <!-- الخيار الفارغ -->
                <option value="1">1 - لا أوافق بشدة</option>
                <option value="2">2 - لا أوافق</option>
                <option value="3">3 - محايد</option>
                <option value="4">4 - أوافق</option>
                <option value="5">5 - أوافق بشدة</option>
            </select>
        </td>
    <?php endfor; ?>
</tr>
                            <!-- السؤال الثالث: النجاح -->
                           <tr>
    <td>أنا ناجح في أداء هذا العمل</td>
    <?php for ($i = 0; $i < count($trait_names); $i++): ?>
        <td>
            <select name="answers[<?php echo $i; ?>][3]" class="form-control">
                <option value="">-- اختر --</option> <!-- الخيار الفارغ -->
                <option value="1">1 - لا أوافق بشدة</option>
                <option value="2">2 - لا أوافق</option>
                <option value="3">3 - محايد</option>
                <option value="4">4 - أوافق</option>
                <option value="5">5 - أوافق بشدة</option>
            </select>
        </td>
    <?php endfor; ?>
</tr>
							<!-- --------------------------------------------------- -->
							<tr>
                                <th style="background-color:blue;color:white;">النمو</th>
                                <th colspan="<?php echo count($trait_names); ?>" class="merged-cell">درجاتي</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- السؤال الأول: النجاح -->
                           <tr>
    <td>أنا ناجح في أداء هذا العمل</td>
    <?php for ($i = 0; $i < count($trait_names); $i++): ?>
        <td>
            <select name="answers[<?php echo $i; ?>][1]" class="form-control">
                <option value="">-- اختر --</option> <!-- الخيار الفارغ -->
                <option value="1">1 - لا أوافق بشدة</option>
                <option value="2">2 - لا أوافق</option>
                <option value="3">3 - محايد</option>
                <option value="4">4 - أوافق</option>
                <option value="5">5 - أوافق بشدة</option>
            </select>
        </td>
    <?php endfor; ?>
</tr>
                            <!-- السؤال الثاني: النجاح -->
                           <tr>
    <td>أنا ناجح في أداء هذا العمل</td>
    <?php for ($i = 0; $i < count($trait_names); $i++): ?>
        <td>
            <select name="answers[<?php echo $i; ?>][2]" class="form-control">
                <option value="">-- اختر --</option> <!-- الخيار الفارغ -->
                <option value="1">1 - لا أوافق بشدة</option>
                <option value="2">2 - لا أوافق</option>
                <option value="3">3 - محايد</option>
                <option value="4">4 - أوافق</option>
                <option value="5">5 - أوافق بشدة</option>
            </select>
        </td>
    <?php endfor; ?>
</tr>
                            <!-- السؤال الثالث: النجاح -->
                          <tr>
    <td>أنا ناجح في أداء هذا العمل</td>
    <?php for ($i = 0; $i < count($trait_names); $i++): ?>
        <td>
            <select name="answers[<?php echo $i; ?>][3]" class="form-control">
                <option value="">-- اختر --</option> <!-- الخيار الفارغ -->
                <option value="1">1 - لا أوافق بشدة</option>
                <option value="2">2 - لا أوافق</option>
                <option value="3">3 - محايد</option>
                <option value="4">4 - أوافق</option>
                <option value="5">5 - أوافق بشدة</option>
            </select>
        </td>
    <?php endfor; ?>
</tr>
							<tr>
                                <th style="background-color:blue;color:white;">الحاجات</th>
                                <th colspan="<?php echo count($trait_names); ?>" class="merged-cell">درجاتي</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- السؤال الأول: النجاح -->
                           <tr>
    <td>أنا ناجح في أداء هذا العمل</td>
    <?php for ($i = 0; $i < count($trait_names); $i++): ?>
        <td>
            <select name="answers[<?php echo $i; ?>][1]" class="form-control">
                <option value="">-- اختر --</option> <!-- الخيار الفارغ -->
                <option value="1">1 - لا أوافق بشدة</option>
                <option value="2">2 - لا أوافق</option>
                <option value="3">3 - محايد</option>
                <option value="4">4 - أوافق</option>
                <option value="5">5 - أوافق بشدة</option>
            </select>
        </td>
    <?php endfor; ?>
</tr>
                            <!-- السؤال الثاني: النجاح -->
                           <tr>
    <td>أنا ناجح في أداء هذا العمل</td>
    <?php for ($i = 0; $i < count($trait_names); $i++): ?>
        <td>
            <select name="answers[<?php echo $i; ?>][2]" class="form-control">
                <option value="">-- اختر --</option> <!-- الخيار الفارغ -->
                <option value="1">1 - لا أوافق بشدة</option>
                <option value="2">2 - لا أوافق</option>
                <option value="3">3 - محايد</option>
                <option value="4">4 - أوافق</option>
                <option value="5">5 - أوافق بشدة</option>
            </select>
        </td>
    <?php endfor; ?>
</tr>
                            <!-- السؤال الثالث: النجاح -->
                            <tr>
    <td>أنا ناجح في أداء هذا العمل</td>
    <?php for ($i = 0; $i < count($trait_names); $i++): ?>
        <td>
            <select name="answers[<?php echo $i; ?>][3]" class="form-control">
                <option value="">-- اختر --</option> <!-- الخيار الفارغ -->
                <option value="1">1 - لا أوافق بشدة</option>
                <option value="2">2 - لا أوافق</option>
                <option value="3">3 - محايد</option>
                <option value="4">4 - أوافق</option>
                <option value="5">5 - أوافق بشدة</option>
            </select>
        </td>
    <?php endfor; ?>
</tr>
                           
                        </tbody>
                    </table>
                </div>
                <button type="submit" class="btn btn-success">إرسال</button>
            </form>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.datatables.net/1.10.21/js/jquery.dataTables.min.js"></script>
<script>
    $(document).ready(function() {
        $('#traitsTable').DataTable();
    });
</script>

</body>
</html>