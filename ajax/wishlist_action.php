<?php
// مسار الملف: ajax/wishlist_action.php
// المكان: داخل مجلد ajax (ملف جديد)
// الوظيفة: إضافة وإزالة المنتجات من المفضلة بدون تحديث الصفحة

session_start();
require_once '../config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'يجب تسجيل الدخول أولاً.', 'code' => 'auth']);
    exit;
}

$userId = $_SESSION['user_id'];
$itemId = isset($_POST['item_id']) ? (int)$_POST['item_id'] : 0;
$itemType = isset($_POST['item_type']) ? $_POST['item_type'] : 'product'; // product, audio, video, package

if ($itemId <= 0 || !in_array($itemType, ['product', 'audio', 'video', 'package'])) {
    echo json_encode(['success' => false, 'message' => 'بيانات غير صالحة.']);
    exit;
}

try {
    // التحقق هل العنصر موجود مسبقاً في المفضلة
    $stmt = $pdo->prepare("SELECT id FROM wishlists WHERE user_id = ? AND wishlistable_type = ? AND wishlistable_id = ?");
    $stmt->execute([$userId, $itemType, $itemId]);
    $exists = $stmt->fetch();

    if ($exists) {
        // إزالة من المفضلة
        $pdo->prepare("DELETE FROM wishlists WHERE id = ?")->execute([$exists['id']]);
        echo json_encode(['success' => true, 'action' => 'removed', 'message' => 'تمت الإزالة من المفضلة']);
    } else {
        // إضافة للمفضلة
        $pdo->prepare("INSERT INTO wishlists (user_id, wishlistable_type, wishlistable_id) VALUES (?, ?, ?)")->execute([$userId, $itemType, $itemId]);
        echo json_encode(['success' => true, 'action' => 'added', 'message' => 'تمت الإضافة إلى المفضلة']);
    }

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'حدث خطأ في الخادم.']);
}
?>