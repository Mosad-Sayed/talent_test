<?php
session_start();
$mysqli = new mysqli("localhost", "root", "", "talent_test");
$mysqli->set_charset("utf8mb4");

if ($mysqli->connect_error) {
    die("فشل الاتصال بقاعدة البيانات: " . $mysqli->connect_error);
}

// معالجة طلبات الحذف والتحديث
if (isset($_GET['action']) && $_GET['action'] == 'delete_user' && isset($_GET['user_id'])) {
    $user_id = intval($_GET['user_id']);
    if ($mysqli->query("DELETE FROM users WHERE id = $user_id")) {
        $_SESSION['message'] = 'تم حذف المستخدم بنجاح!';
    } else {
        $_SESSION['message'] = 'فشل في حذف المستخدم.';
    }
}

if (isset($_POST['update_notes'])) {
    $user_id = intval($_POST['user_id']);
    $notes = $mysqli->real_escape_string($_POST['notes']);

    if ($mysqli->query("UPDATE user_results SET notes = '$notes' WHERE user_id = $user_id")) {
        $_SESSION['message'] = 'تم تحديث الملاحظات بنجاح!';
    } else {
        $_SESSION['message'] = 'فشل في تحديث الملاحظات.';
    }

    header('Location: admin_dashboard.php');
    exit();
}

// جلب بيانات المستخدمين
$users_result = $mysqli->query("SELECT id, name, email, student_code FROM users");

// جلب نتائج الطلاب
$results = $mysqli->query("
    SELECT ur.user_id, u.name, u.student_code, ur.trait_name, ur.total_score, ur.classification, ur.notes 
    FROM user_results ur 
    JOIN users u ON ur.user_id = u.id
");

$mysqli->close();
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>لوحة التحكم</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
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
            max-width: 1200px;
            margin: 30px auto;
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
        .btn-block {
            margin-bottom: 20px;
        }
        .btn-block button {
            font-size: 1.2rem;
            border-radius: 50px;
            padding: 10px 30px;
            transition: all 0.3s ease-in-out;
        }
        .btn-primary {
            background-color: #007bff;
            border: none;
        }
        .btn-primary:hover {
            background-color: #0056b3;
        }
        .btn-success {
            background-color: #28a745;
            border: none;
        }
        .btn-success:hover {
            background-color: #218838;
        }
        .table-responsive {
            margin-top: 20px;
            border-radius: 15px;
            overflow: hidden;
        }
        table {
            background-color: #fff;
            color: #333;
        }
        table th, table td {
            text-align: center;
            vertical-align: middle;
        }
        table thead {
            background-color: #0056b3;
            color: white;
        }
        .alert {
            position: fixed;
            top: 20px;
            right: 20px;
            width: 300px;
            display: none;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="card">
        <div class="card-header text-center">
            <h3>لوحة التحكم</h3>
        </div>
        <div class="card-body">
            <!-- أزرار التحكم -->
            <div class="btn-block text-center">
                <button class="btn btn-primary" onclick="showBlock('users')">عرض المستخدمين</button>
                <button class="btn btn-success" onclick="showBlock('results')">عرض نتائج الطلاب</button>
            </div>

            <!-- بلوك عرض المستخدمين -->
            <div id="usersBlock" class="block" style="display: none;">
                <h4 class="text-center">عرض المستخدمين</h4>
                <div class="table-responsive">
                    <table class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th>اسم المستخدم</th>
                                <th>البريد الإلكتروني</th>
                                <th>كود الطالب</th>
                                <th>خيارات</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($user = $users_result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($user['name']); ?></td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td><?php echo htmlspecialchars($user['student_code']); ?></td>
                                <td>
                                    <a href="?action=delete_user&user_id=<?php echo $user['id']; ?>" class="btn btn-danger btn-sm">حذف</a>
                                    <!-- يمكن إضافة رابط للتعديل هنا -->
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- بلوك عرض نتائج الطلاب -->
            <div id="resultsBlock" class="block" style="display: none;">
                <h4 class="text-center">عرض نتائج الطلاب</h4>
                <form method="post" action="">
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered">
                            <thead>
                                <tr>
                                    <th>اسم الطالب</th>
                                    <th>كود الطالب</th>
                                    <th>اسم السمة</th>
                                    <th>إجمالي النقاط</th>
                                    <th>التصنيف</th>
                                    <th>ملاحظات</th>
                                    <th>تحديث</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $current_user_id = null;
                                $current_notes = '';
                                while ($result = $results->fetch_assoc()):
                                    if ($current_user_id != $result['user_id']):
                                        if ($current_user_id !== null): ?>
                                            <tr>
                                                <td colspan="7">
                                                    <strong>ملاحظات للطالب <?php echo htmlspecialchars($current_user_id); ?>:</strong>
                                                    <form method="post" action="">
                                                        <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($current_user_id); ?>">
                                                        <input type="text" name="notes" value="<?php echo htmlspecialchars($current_notes); ?>" class="form-control">
                                                        <button type="submit" name="update_notes" class="btn btn-primary btn-sm">تحديث الملاحظات</button>
                                                    </form>
                                                </td>
                                            </tr>
                                        <?php endif; 
                                        $current_user_id = $result['user_id'];
                                        $current_notes = $result['notes'];
                                    endif; ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($result['name']); ?></td>
                                    <td><?php echo htmlspecialchars($result['student_code']); ?></td>
                                    <td><?php echo htmlspecialchars($result['trait_name']); ?></td>
                                    <td><?php echo htmlspecialchars($result['total_score']); ?></td>
                                    <td><?php echo htmlspecialchars($result['classification']); ?></td>
                                    <td><?php echo htmlspecialchars($result['notes']); ?></td>
                                    <td></td>
                                </tr>
                                <?php endwhile; ?>
                                <?php if ($current_user_id !== null): ?>
                                    <tr>
                                        <td colspan="7">
                                            <strong>ملاحظات للطالب <?php echo htmlspecialchars($current_user_id); ?>:</strong>
                                            <form method="post" action="">
                                                <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($current_user_id); ?>">
                                                <input type="text" name="notes" value="<?php echo htmlspecialchars($current_notes); ?>" class="form-control">
                                                <button type="submit" name="update_notes" class="btn btn-primary btn-sm">تحديث الملاحظات</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- جافا سكريبت لتبديل بين البلوكات وعرض الرسائل -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script>
function showBlock(blockId) {
    document.querySelectorAll('.block').forEach(function(block) {
        block.style.display = 'none';
    });
    document.getElementById(blockId + 'Block').style.display = 'block';
}

// عرض الرسائل من الجلسة
<?php if (isset($_SESSION['message'])): ?>
    showAlert('<?php echo $_SESSION['message']; ?>', 'success');
    <?php unset($_SESSION['message']); ?>
<?php endif; ?>

function showAlert(message, type) {
    var alert = document.createElement('div');
    alert.className = 'alert alert-' + type;
    alert.textContent = message;
    document.body.appendChild(alert);
    setTimeout(function() {
        alert.remove();
        window.location.href = 'admin_dashboard.php';
    }, 5000);
}
</script>

</body>
</html>
