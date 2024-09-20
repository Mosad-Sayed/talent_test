<!DOCTYPE html>
<html lang="ar">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>برنامج اكتشاف المواهب</title>
  <style>
    /* استيراد خط مميز */
    @import url('https://fonts.googleapis.com/css2?family=Cairo:wght@400;600&display=swap');

    /* تنسيقات عامة */
    body {
      font-family: 'Cairo', sans-serif;
      margin: 0;
      padding: 0;
      direction: rtl;
      text-align: right;
      background: linear-gradient(135deg, #1a73e8, #4285f4);
      color: #fff;
    }

    h1, h2, h3 {
      color: #fff;
    }

    p {
      color: #f0f0f0;
    }

    a {
      text-decoration: none;
    }

    /* الهيرو سكشن */
    .hero {
      height: 100vh;
      display: flex;
      justify-content: center;
      align-items: center;
      background: linear-gradient(135deg, #42a5f5, #1e88e5);
      text-align: center;
      padding: 0 20px;
    }

    .hero-content h1 {
      font-size: 3.5rem;
      margin-bottom: 20px;
    }

    .hero-content p {
      font-size: 1.4rem;
      margin-bottom: 30px;
    }

    .cta-button {
      display: inline-block;
      padding: 15px 40px;
      background-color: #fff;
      color: #4285f4;
      font-size: 1.3rem;
      font-weight: bold;
      border-radius: 50px;
      transition: background-color 0.3s, color 0.3s;
      cursor: pointer;
    }

    .cta-button:hover {
      background-color: #4285f4;
      color: #fff;
    }

    /* قسم المراحل */
    .steps-section {
      padding: 60px 20px;
      background-color: #222;
      text-align: center;
    }

    .steps-section h2 {
      font-size: 2.8rem;
      margin-bottom: 40px;
    }

    .steps-container {
      display: flex;
      justify-content: space-around;
      flex-wrap: wrap;
    }

    .step {
      background-color: #333;
      padding: 20px;
      border-radius: 15px;
      box-shadow: 0px 5px 15px rgba(0, 0, 0, 0.3);
      margin: 20px;
      flex: 1 1 30%;
      min-width: 250px;
      transition: transform 0.3s, box-shadow 0.3s;
    }

    .step:hover {
      transform: translateY(-10px);
      box-shadow: 0px 10px 20px rgba(0, 0, 0, 0.5);
    }

    .step h3 {
      font-size: 1.8rem;
      margin-bottom: 15px;
    }

    .step p {
      font-size: 1.2rem;
      color: #ddd;
    }

    /* نافذة التعليمات */
    .modal {
      display: none;
      position: fixed;
      z-index: 1000;
      left: 0;
      top: 0;
      width: 100%;
      height: 100%;
      overflow: auto;
      background-color: rgba(0, 0, 0, 0.7);
      justify-content: center;
      align-items: center;
    }

    .modal-content {
      background-color: #777; /* اللون الرمادي */
      margin: auto;
      padding: 20px;
      border: 1px solid #888;
      width: 80%;
      max-width: 600px;
      border-radius: 15px;
      text-align: right;
      color: #000; /* النص باللون الأسود */
    }

    .close {
      color: #aaa;
      float: left;
      font-size: 28px;
      font-weight: bold;
    }

    .close:hover,
    .close:focus {
      color: #fff;
      text-decoration: none;
      cursor: pointer;
    }

    .progress-bar {
      width: 100%;
      background-color: #ddd;
      border-radius: 25px;
      overflow: hidden;
      margin-top: 20px;
    }

    .progress {
      height: 20px;
      width: 0;
      background-color: #4caf50;
      transition: width 0.1s ease-in-out;
    }

    .counter {
      text-align: center;
      margin-top: 10px;
      font-size: 1.2rem;
      color: #fff;
    }

    /* تذييل الصفحة */
    .footer {
      background-color: #111;
      color: #fff;
      text-align: center;
      padding: 20px 0;
      margin-top: 40px;
    }

    .footer p {
      margin: 0;
    }
  </style>
</head>
<body>

  <!-- قسم الهيدر والعنوان الرئيسي -->
  <header class="hero">
    <div class="hero-content">
      <h1>برنامج اكتشاف المواهب</h1>
      <p>اختبر قدراتك ومواهبك واكتشف الجوانب التي تميزك. سجل الآن وابدأ رحلتك نحو اكتشاف الذات.</p>
      <button class="cta-button" id="openModal">ابدأ الآن</button>
    </div>
  </header>

  <!-- نافذة التعليمات -->
  <div id="instructionsModal" class="modal">
    <div class="modal-content">
      <span class="close" id="closeModal">&times;</span>
      <h2>تعليمات البرنامج</h2>
      <p>1. قم بتسجيل الدخول أو إنشاء حساب جديد.</p>
      <p>2. اختر 15 سمة تعكس قدراتك.</p>
      <p>3. حدد 7 سمات من السمات المختارة.</p>
      <p>4. أجب على الأسئلة لتقييم نفسك.</p>
      <p>5. بعد الانتهاء، سيتم عرض نتيجة التحليل.</p>
      <div class="progress-bar">
        <div class="progress" id="progress"></div>
      </div>
      <div class="counter" id="counter">0%</div>
    </div>
  </div>

  <!-- قسم المراحل -->
  <section id="steps" class="steps-section">
    <h2>كيف يعمل البرنامج</h2>
    <div class="steps-container">
      <div class="step">
        <h3>1. تسجيل الدخول</h3>
        <p>ابدأ بإنشاء حساب أو تسجيل الدخول للوصول إلى الاختبار.</p>
      </div>
      <div class="step">
        <h3>2. اختيار السمات</h3>
        <p>قم باختيار 15 سمة تعتقد أنها تميزك، ثم حدد 7 سمات من بينهم.</p>
      </div>
      <div class="step">
        <h3>3. الإجابة على الأسئلة</h3>
        <p>أجب على الأسئلة التي تظهر بناءً على السمات التي اخترتها.</p>
      </div>
      <div class="step">
        <h3>4. استلام النتيجة</h3>
        <p>بعد الإجابة على الأسئلة، ستحصل على تحليل شامل لمواهبك وقدراتك.</p>
      </div>
    </div>
  </section>

  <!-- تذييل الصفحة -->
  <footer class="footer">
    <p>© 2024 برنامج اكتشاف المواهب. جميع الحقوق محفوظة.</p>
  </footer>

  <script>
    // فتح نافذة التعليمات
    document.getElementById("openModal").onclick = function() {
      document.getElementById("instructionsModal").style.display = "flex";
      let progressBar = document.getElementById("progress");
      let counter = document.getElementById("counter");
      let width = 0;
      let interval = setInterval(function() {
        if (width >= 100) {
          clearInterval(interval);
          setTimeout(() => {
            window.location.href = "homepage.php"; // الانتقال بعد انتهاء البروسس بار
          }, 500); // الانتظار 0.5 ثانية
        } else {
          width++;
          progressBar.style.width = width + '%';
          counter.textContent = width + '%';
        }
      }, 100); // سرعة التقدم (100 مللي ثانية) لجعلها أبطأ
    };

    // إغلاق نافذة التعليمات
    document.getElementById("closeModal").onclick = function() {
      document.getElementById("instructionsModal").style.display = "none";
    };

    // إغلاق النافذة عند النقر خارج المحتوى
    window.onclick = function(event) {
      const modal = document.getElementById("instructionsModal");
      if (event.target == modal) {
        modal.style.display = "none";
      }
    };
  </script>
</body>
</html>
