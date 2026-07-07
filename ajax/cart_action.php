<?php
// مسار الملف: ajax/cart_action.php
// المكان: داخل مجلد ajax
// الوظيفة: معالجة سلة التسوق والكوبونات بشكل ديناميكي (AJAX API)

session_start();
require_once '../config.php';
header('Content-Type: application/json');

$action = isset($_POST['action']) ? $_POST['action'] : '';

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

$response = ['success' => false, 'message' => ''];

try {
    $productId = (int)($_POST['product_id'] ?? 0);
    $qty = (int)($_POST['quantity'] ?? 1);

    // 1. معالجة الإجراءات
    if ($action === 'add') {
        if ($productId > 0) {
            // التحقق من المخزون أولاً
            $stmt = $pdo->prepare("SELECT stock_quantity FROM products WHERE id = ?");
            $stmt->execute([$productId]);
            $stock = $stmt->fetchColumn();

            if ($stock !== false && $stock >= $qty) {
                if (isset($_SESSION['cart'][$productId])) {
                    $newQty = $_SESSION['cart'][$productId] + $qty;
                    $_SESSION['cart'][$productId] = min($newQty, $stock);
                } else {
                    $_SESSION['cart'][$productId] = $qty;
                }
                $response['success'] = true;
                $response['message'] = 'تمت الإضافة للسلة بنجاح';
            } else {
                $response['message'] = 'الكمية المطلوبة غير متوفرة في المخزون';
            }
        }
    } 
    elseif ($action === 'update') {
        if ($productId > 0 && $qty > 0) {
            // التحقق من المخزون
            $stmt = $pdo->prepare("SELECT stock_quantity FROM products WHERE id = ?");
            $stmt->execute([$productId]);
            $stock = $stmt->fetchColumn();

            $_SESSION['cart'][$productId] = min($qty, $stock);
            $response['success'] = true;
        }
    } 
    elseif ($action === 'remove') {
        if ($productId > 0 && isset($_SESSION['cart'][$productId])) {
            unset($_SESSION['cart'][$productId]);
            $response['success'] = true;
            $response['message'] = 'تم حذف المنتج من السلة';
        }
    } 
    elseif ($action === 'apply_coupon') {
        $code = strtoupper(trim($_POST['coupon_code'] ?? ''));
        $stmt = $pdo->prepare("SELECT * FROM coupons WHERE code = ? AND is_active = 1 AND (starts_at IS NULL OR starts_at <= NOW()) AND (expires_at IS NULL OR expires_at >= NOW())");
        $stmt->execute([$code]);
        $coupon = $stmt->fetch();
        
        if ($coupon) {
            $_SESSION['coupon_code'] = $coupon['code'];
            $response['success'] = true;
            $response['message'] = 'تم تطبيق كود الخصم بنجاح!';
        } else {
            unset($_SESSION['coupon_code']);
            $response['message'] = 'الكوبون غير صالح أو منتهي الصلاحية';
        }
    } 
    elseif ($action === 'remove_coupon') {
        unset($_SESSION['coupon_code']);
        $response['success'] = true;
        $response['message'] = 'تم إزالة الكوبون';
    }

    // 2. حساب المجاميع الجديدة لإعادتها للواجهة الأمامية
    $subTotal = 0;
    $cartItems = $_SESSION['cart'];
    $totalItems = array_sum($cartItems);
    $itemLineTotal = 0;
    
    if (!empty($cartItems)) {
        $ids = implode(',', array_keys($cartItems));
        $stmt = $pdo->query("SELECT id, price, stock_quantity FROM products WHERE id IN ($ids)");
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($products as $p) {
            $q = $_SESSION['cart'][$p['id']];
            $lineTotal = $p['price'] * $q;
            $subTotal += $lineTotal;
            if ($p['id'] == $productId) {
                $itemLineTotal = $lineTotal;
            }
        }
    } else {
        unset($_SESSION['coupon_code']); // إزالة الكوبون إذا فرغت السلة
    }
    
    $shippingCost = ($subTotal >= 200 || $subTotal == 0) ? 0 : 25;
    $couponDiscount = 0;
    
    // التحقق من الكوبون المحفوظ في الجلسة وحسابه
    if (isset($_SESSION['coupon_code']) && $subTotal > 0) {
        $stmt = $pdo->prepare("SELECT * FROM coupons WHERE code = ?");
        $stmt->execute([$_SESSION['coupon_code']]);
        $coupon = $stmt->fetch();
        
        if ($coupon && $subTotal >= $coupon['min_order_amount']) {
            if ($coupon['discount_type'] === 'percentage') {
                $couponDiscount = $subTotal * ($coupon['discount_value'] / 100);
                if ($coupon['max_discount'] && $couponDiscount > $coupon['max_discount']) {
                    $couponDiscount = $coupon['max_discount'];
                }
            } else {
                $couponDiscount = $coupon['discount_value'];
            }
        } else {
            unset($_SESSION['coupon_code']); // الحد الأدنى غير مستوفى
        }
    }
    
    $total = max(0, $subTotal + $shippingCost - $couponDiscount);
    
    $response['item_id'] = $productId;
    $response['item_line_total'] = number_format($itemLineTotal, 2);
    $response['totals'] = [
        'count' => $totalItems,
        'subTotal' => number_format($subTotal, 2),
        'shipping' => $shippingCost == 0 ? 'مجاني' : number_format($shippingCost, 2) . ' ر.س',
        'discount' => number_format($couponDiscount, 2),
        'total' => number_format($total, 2)
    ];
    
    echo json_encode($response);
} catch(Exception $e) {
    echo json_encode(['success' => false, 'message' => 'حدث خطأ في الخادم']);
}
?>