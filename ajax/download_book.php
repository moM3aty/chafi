<?php
// مسار الملف: ajax/download_book.php
// الوظيفة: معالجة تحميل الكتب بشكل مستقل ونقي لضمان عدم تلف ملفات الـ PDF

session_start();
require_once '../config.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// جلب بيانات الكتاب
$stmt = $pdo->prepare("SELECT * FROM books WHERE id = ? AND is_active = 1");
$stmt->execute([$id]);
$book = $stmt->fetch();

if (!$book) {
    die("عذراً، الكتاب غير موجود.");
}

$isFree = (float)$book['price'] <= 0;
$hasPurchased = false;
$isAdmin = isset($_SESSION['user_role']) && in_array($_SESSION['user_role'], ['Admin', 'SuperAdmin']);

// التحقق من الصلاحيات للمحتوى المدفوع
if (isset($_SESSION['user_id']) && !$isFree && !$isAdmin) {
    $stmtCheck = $pdo->prepare("
        SELECT oi.id 
        FROM order_items oi 
        JOIN orders o ON oi.order_id = o.id 
        WHERE o.user_id = ? 
          AND oi.item_type = 'book' 
          AND oi.item_id = ? 
          AND o.status IN ('Processing', 'Shipped', 'Delivered', 'Completed')
    ");
    $stmtCheck->execute([$_SESSION['user_id'], $id]);
    if ($stmtCheck->fetch()) {
        $hasPurchased = true;
    }
}

$canDownload = $isFree || $hasPurchased || $isAdmin;

if ($canDownload) {
    // المسار الفعلي للملف على السيرفر
    $filePath = __DIR__ . '/../' . ltrim($book['book_file_url'], '/');
    
    if (!empty($book['book_file_url']) && file_exists($filePath)) {
        // زيادة عدد التحميلات
        $pdo->prepare("UPDATE books SET download_count = download_count + 1 WHERE id = ?")->execute([$id]);
        
        // تنظيف الـ Buffer تماماً لضمان عدم تسرب أي كود مسافات أو HTML
        if (ob_get_level()) {
            ob_end_clean();
        }

        // تحديد نوع الملف بناءً على الامتداد
        $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        $contentType = 'application/pdf';
        if ($ext === 'epub') $contentType = 'application/epub+zip';
        elseif (in_array($ext, ['doc', 'docx'])) $contentType = 'application/msword';

        // إرسال الهيدرات الصحيحة للمتصفح لتحميل الملف بشكل نقي
        header('Content-Description: File Transfer');
        header('Content-Type: ' . $contentType);
        header('Content-Disposition: attachment; filename="' . basename($filePath) . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($filePath));

        // قراءة الملف وإرساله للعميل
        readfile($filePath);
        exit;
    } else {
        die("عذراً، ملف الكتاب غير موجود على الخادم حالياً.");
    }
} else {
    die("غير مصرح لك بتحميل هذا الكتاب.");
}
?>