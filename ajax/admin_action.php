<?php
// مسار الملف: ajax/admin_action.php
// المكان: داخل مجلد ajax
// الوظيفة: معالجة عمليات الإدارة (تحديث حالة، حذف، إلخ)

session_start();
require_once '../config.php';

// حماية الملف من أي وصول غير مصرح
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['Admin', 'SuperAdmin'])) {
    die("Unauthorized Access");
}

$action = isset($_POST['action']) ? $_POST['action'] : '';

try {
    if ($action == 'update_order_status') {
        $orderId = (int)$_POST['order_id'];
        $newStatus = $_POST['status'];

        $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
        $stmt->execute([$newStatus, $orderId]);

        // يمكننا إضافة سجل في order_status_history هنا مستقبلاً
        
        header("Location: ../index.php?page=admin_orders");
        exit;
    } 
    elseif ($action == 'delete_product') {
        $productId = (int)$_POST['product_id'];

        $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
        $stmt->execute([$productId]);

        header("Location: ../index.php?page=admin_products");
        exit;
    }

} catch (PDOException $e) {
    die("حدث خطأ في قاعدة البيانات: " . $e->getMessage());
}
?>