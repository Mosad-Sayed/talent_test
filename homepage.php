<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>نقاط القوة</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(to right, #0f2027, #203a43, #2c5364); /* تدرج ألوان عصري */
            font-family: 'Tajawal', sans-serif;
            color: #fff;
            direction: rtl; /* تحديد اتجاه النص من اليمين لليسار */
            text-align: right; /* محاذاة النصوص لليمين */
        }
        .container {
            margin-top: 50px;
        }
        .card {
            border: none;
            border-radius: 12px;
            background: #fff;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }
        .card:hover {
            transform: scale(1.03);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
        }
        .card-header {
            background: linear-gradient(135deg, #007bff, #28a745);
            color: #fff;
            border-radius: 12px 12px 0 0;
            text-align: center;
            padding: 1.5rem;
        }
        .form-group label {
            color: #333;
            font-weight: bold;
        }
        .form-control {
            border-radius: 30px;
            padding: 15px;
            font-size: 1rem;
        }
        .form-control:focus {
            border-color: #007bff;
            box-shadow: 0 0 10px rgba(0, 123, 255, 0.25);
        }
        .btn-success {
            background-color: #28a745;
            border-radius: 30px;
            font-size: 1.2em;
            font-weight: bold;
            padding: 12px 30px;
            transition: background-color 0.3s ease, box-shadow 0.3s ease;
        }
        .btn-success:hover {
            background-color: #218838;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.15);
        }
    </style>
</head>
<body>

<div class="container">
    <div class="card shadow-lg">
        <div class="card-header text-center">
            <h3>مقياس نقاط القوة</h3>
        </div>
        <div class="card-body">
            <form id="userForm" action="traits.php" method="POST">
                <div class="form-group">
                    <label for="name">الاسم</label>
                    <input type="text" class="form-control" id="name" name="name" placeholder="أدخل اسمك" required>
                </div>
                <div class="form-group">
                    <label for="email">البريد الإلكتروني</label>
                    <input type="email" class="form-control" id="email" name="email" placeholder="أدخل بريدك الإلكتروني" required>
                </div>
                <div class="form-group">
                    <label for="student_code">كود الطالب</label>
                    <input type="text" class="form-control" id="student_code" name="student_code" placeholder="أدخل كود الطالب" required>
                </div>
                <button type="submit" class="btn btn-success btn-block">بدء الاختبار</button>
            </form>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>