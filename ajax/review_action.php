<?php
// مسار الملف: ajax/review_action.php
// المكان: داخل مجلد ajax (ملف جديد)
// الوظيفة: استقبال تقييمات العملاء

session_start();
require_once '../config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'يجب تسجيل الدخول لإضافة تقييم.']);
    exit;
}

$userId = $_SESSION['user_id'];
$itemId = isset($_POST['item_id']) ? (int)$_POST['item_id'] : 0;
$itemType = isset($_POST['item_type']) ? $_POST['item_type'] : 'product';
$rating = isset($_POST['rating']) ? (int)$_POST['rating'] : 0;
$reviewText = isset($_POST['review_text']) ? trim($_POST['review_text']) : '';

if ($itemId <= 0 || $rating < 1 || $rating > 5) {
    echo json_encode(['success' => false, 'message' => 'الرجاء تحديد التقييم بالنجوم بشكل صحيح.']);
    exit;
}

try {
    // التحقق إذا كان المستخدم قد قيّم هذا المنتج مسبقاً
    $stmt = $pdo->prepare("SELECT id FROM reviews WHERE user_id = ? AND reviewable_type = ? AND reviewable_id = ?");
    $stmt->execute([$userId, $itemType, $itemId]);
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'لقد قمت بتقييم هذا المنتج مسبقاً.']);
        exit;
    }

    // جلب إعداد الاعتماد التلقائي
    $autoApprove = $pdo->query("SELECT setting_value FROM settings WHERE setting_key = 'auto_approve_reviews'")->fetchColumn();
    $isApproved = ($autoApprove === '1') ? 1 : 0;

    // إضافة التقييم
    $stmt = $pdo->prepare("INSERT INTO reviews (user_id, reviewable_type, reviewable_id, rating, review_text, is_approved) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$userId, $itemType, $itemId, $rating, $reviewText, $isApproved]);

    $msg = $isApproved ? 'شرياً لك! تم نشر تقييمك بنجاح.' : 'شكراً لك! تقييمك قيد المراجعة وسيتم نشره قريباً.';
    echo json_encode(['success' => true, 'message' => $msg]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'حدث خطأ في الخادم.']);
}
?>