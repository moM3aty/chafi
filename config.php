<?php
// مسار الملف: config.php
// المكان: يُحفظ في المجلد الرئيسي للمشروع
// الوظيفة: إعدادات الاتصال بقاعدة بيانات MySQL

// بيانات الاستضافة الخاصة بك
define('DB_HOST', 'localhost');
define('DB_USER', 'u582652079_admin'); // استبدلها باسم المستخدم الفعلي
define('DB_PASS', 'Chafi@Store#2026'); // استبدلها بكلمة المرور الفعلية
define('DB_NAME', 'u582652079_chafidb'); // استبدلها باسم قاعدة البيانات الفعلي

try {
    // الاتصال بقاعدة البيانات
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS);
    
    // تفعيل وضع إظهار الأخطاء للحماية وتتبع الأخطاء
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // رسالة خطأ تظهر في حال فشل الاتصال
    die("<div style='font-family: Arial; padding: 20px; background: #fee2e2; color: #b91c1c; border-radius: 10px; margin: 50px auto; max-width: 600px; text-align: right;' dir='rtl'>
            <h3 style='margin-top:0'>فشل الاتصال بقاعدة البيانات ❌</h3>
            <p>تأكد من إدخال بيانات الاتصال الصحيحة في ملف <b>config.php</b>، وتأكد من أن اسم القاعدة والمستخدم متطابقان مع إعدادات الاستضافة.</p>
            <p dir='ltr' style='background: #fff; padding: 10px; border-radius: 5px; color: #000;'>" . htmlspecialchars($e->getMessage()) . "</p>
         </div>");
}
?>