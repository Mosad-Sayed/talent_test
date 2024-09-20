<?php
// الاتصال بقاعدة البيانات
$mysqli = new mysqli("fdb28.awardspace.net", "4519643_request", "Mosad@55555", "4519643_request");
$mysqli->set_charset("utf8mb4");

if ($mysqli->connect_error) {
    die("فشل الاتصال بقاعدة البيانات: " . $mysqli->connect_error);
}

// التحقق من طلب POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $selected_traits = $_POST['traits']; // السمات المختارة (IDs)
    if (count($selected_traits) !== 15) {
        die("يجب عليك اختيار 15 سمات");
    }
    $user_id = $_POST['user_id'];

    // استعلام لجلب أسماء السمات المختارة
    $trait_ids = implode(",", $selected_traits);
    $result = $mysqli->query("SELECT id, trait_name FROM traits WHERE id IN ($trait_ids)");
    
    // قائمة السمات المختارة
    $selected_traits_names = [];
    while ($row = $result->fetch_assoc()) {
        $selected_traits_names[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تصفية السمات</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(to right, #0f2027, #203a43, #2c5364); /* تدرج ألوان عصري */
            font-family: 'Tajawal', sans-serif;
            color: #fff;
        }
        .container {
            margin-top: 50px;
        }
        .card {
            border: none;
            border-radius: 12px;
            background: #fff;
            height: 100%; /* ضمان أن جميع الكاردات تكون بنفس الارتفاع */
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        .card:hover {
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
        }
        .card-title {
            font-size: 1em; /* تصغير حجم الخط للعناوين */
            font-weight: bold;
            color: #28a745;
            text-transform: uppercase;
            margin-bottom: 15px; /* مسافة بين العنوان والـ Checkbox */
        }
        .form-check {
            margin-top: auto; /* دفع الـ Checkbox إلى الأسفل */
            text-align: center; /* جعل الـ Checkbox في المنتصف */
        }
        .form-check-input {
            margin-left: 0;
            transform: scale(1.5); /* ضبط حجم الـ Checkbox */
            cursor: pointer;
        }
        .card-body {
            padding: 1.5rem;
            background: #f7f9fc;
            border-radius: 10px;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            text-align: center;
        }
        .btn-primary, .btn-success {
            border-radius: 30px;
            font-size: 1.2em;
            font-weight: bold;
            padding: 12px 30px;
            transition: background-color 0.3s ease, box-shadow 0.3s ease;
        }
        .btn-primary {
            background-color: #007bff;
            border-color: #007bff;
        }
        .btn-primary:hover {
            background-color: #0056b3;
            border-color: #004085;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.15);
        }
        .btn-success {
            background-color: #28a745;
            border-color: #28a745;
        }
        .btn-success:hover {
            background-color: #218838;
            border-color: #1e7e34;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.15);
        }
        .card-header {
            background: linear-gradient(135deg, #007bff, #28a745);
            color: #fff;
            border-radius: 12px 12px 0 0;
            text-align: center;
            padding: 1.5rem 0;
        }
        h3 {
            font-size: 1.8rem;
            font-weight: bold;
        }
        .row {
            display: flex;
            flex-wrap: wrap;
        }
        .col-md-3, .col-md-4, .col-md-5 {
            display: flex;
            justify-content: center;
            margin-bottom: 20px;
        }
        @media (min-width: 992px) {
            .col-md-3 {
                flex: 0 0 20%;
                max-width: 20%;
            }
            .col-md-4 {
                flex: 0 0 25%;
                max-width: 25%;
            }
            .col-md-5 {
                flex: 0 0 33.33%;
                max-width: 33.33%;
            }
        }

               /* تعديل مكان العداد ليكون في أعلى الصفحة */
#traitCounter {
    position: fixed;
    top: 20px; /* تحديد المسافة من الأعلى */
    left: 50%;
    transform: translateX(-50%); /* تعديل المركز على محور X فقط */
    background-color: #007bff;
    color: white;
    padding: 15px 30px;
    border-radius: 50px;
    font-size: 24px;
    font-weight: bold;
    z-index: 999;
    text-align: center;
    box-shadow: 0 10px 20px rgba(0, 0, 0, 0.3);
    transition: background-color 0.3s ease, transform 0.3s ease;
    animation: pulse 1.5s infinite; /* إضافة تأثير الحركة */
}


       /* تأثير النبض لجعل العداد ينبض */
@keyframes pulse {
    0% {
        transform: translateX(-50%) scale(1); /* تم تعديل translate ليؤثر على المحور X فقط */
    }
    50% {
        transform: translateX(-50%) scale(1.1);
    }
    100% {
        transform: translateX(-50%) scale(1);
    }
}

    </style>
</head>
<body>

<!-- العداد -->

<div class="container">
    <div class="card shadow-lg">
        <div class="card-header text-center bg-primary text-white">
          <!--  <h3>اختر أبرز 7 سمات</h3> -->
		  <div id="traitCounter">اختر ابرز 7 سمات : 7 / 0 </div>

        </div>
        <div class="card-body">
            <form id="finalTraitsForm" action="questions.php" method="POST">
    <input type="hidden" name="user_id" value="<?php echo $user_id; ?>">
    <div class="row">
        <?php foreach ($selected_traits_names as $trait): ?>
            <div class="col-md-4"> <!-- 3 كاردات في كل صف -->
                <div class="card shadow-sm trait-card" onclick="toggleCheckbox(this)">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo $trait['trait_name']; ?></h5>
                        <div class="form-check">
                            <input type="checkbox" name="final_traits[]" value="<?php echo $trait['id']; ?>" class="form-check-input trait-checkbox">
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <div class="buttons-container mt-4">
        <button type="button" id="finalFilterBtn" class="btn btn-primary">تأكيد السمات</button>
        <button type="submit" class="btn btn-success" disabled id="submitFinalTraits">استمرار</button>
    </div>
</form>
        </div>
    </div>
</div>

<script>
    var maxTraits = 7;
    var checkedCount = 0;

    // تحديث العداد
    function updateCounter() {
        var traitCounter = document.getElementById('traitCounter');
        traitCounter.textContent = "عدد السمات المختارة: " + checkedCount + "/" + maxTraits;
        
        // تغيير لون خلفية العداد إذا تم اختيار 7 سمات
        if (checkedCount === maxTraits) {
            traitCounter.style.backgroundColor = '#28a745'; // لون أخضر
        } else {
            traitCounter.style.backgroundColor = '#007bff'; // اللون الأصلي
        }
    }

    // دالة لتغيير حالة الـ Checkbox عند الضغط على الكارد
    function toggleCheckbox(card) {
        var checkbox = card.querySelector('.trait-checkbox');
        
        if (!checkbox.checked && checkedCount < maxTraits) {
            checkbox.checked = true;
            checkedCount++;
        } else if (checkbox.checked) {
            checkbox.checked = false;
            checkedCount--;
        } else {
            alert("لقد تجاوزت الحد الأقصى لعدد السمات.");
        }

        updateCounter();
        document.getElementById('submitFinalTraits').disabled = checkedCount !== maxTraits;
    }

    // زر تأكيد السمات
    document.getElementById('finalFilterBtn').addEventListener('click', function() {
        if (checkedCount !== maxTraits) {
            alert("يجب عليك اختيار " + maxTraits + " سمات.");
        } else {
            document.getElementById('submitFinalTraits').disabled = false;
        }
    });

    // تحديث العداد في البداية
    updateCounter();
</script>

</body>
</html>
