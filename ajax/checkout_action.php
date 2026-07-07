<?php
// مسار الملف: ajax/checkout_action.php
// الوظيفة: معالجة الطلب النهائي مع احتساب الكوبونات وحفظه بالـ Database بدقة

session_start();
require_once '../config.php';

if (!isset($_SESSION['user_id']) || empty($_SESSION['cart'])) {
    header("Location: ../index.php");
    exit;
}

try {
    // 1. حساب المجاميع من السلة والمخزون
    $cartItems = $_SESSION['cart'];
    $subTotal = 0;
    
    $ids = implode(',', array_keys($cartItems));
    $stmt = $pdo->query("SELECT id, name, price, stock_quantity, image_url FROM products WHERE id IN ($ids)");
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $productsDict = [];
    foreach ($products as $p) {
        $productsDict[$p['id']] = $p;
        $qty = min($cartItems[$p['id']], $p['stock_quantity']); // تأمين عدم تجاوز المخزون
        $subTotal += $p['price'] * $qty;
    }

    $shippingCost = $subTotal >= 200 ? 0 : 25;
    
    // 2. التحقق وتطبيق الكوبون بدقة
    $couponId = null;
    $discountAmount = 0;

    if (isset($_SESSION['coupon_code']) && $subTotal > 0) {
        $cStmt = $pdo->prepare("SELECT id, discount_type, discount_value, max_discount, min_order_amount FROM coupons WHERE code = ? AND is_active = 1 AND (expires_at IS NULL OR expires_at >= NOW())");
        $cStmt->execute([$_SESSION['coupon_code']]);
        $coupon = $cStmt->fetch();

        if ($coupon && $subTotal >= $coupon['min_order_amount']) {
            $couponId = $coupon['id'];
            if ($coupon['discount_type'] === 'percentage') {
                $discountAmount = $subTotal * ($coupon['discount_value'] / 100);
                if ($coupon['max_discount'] && $discountAmount > $coupon['max_discount']) {
                    $discountAmount = $coupon['max_discount'];
                }
            } else {
                $discountAmount = $coupon['discount_value'];
            }
            
            // زيادة عداد استخدام الكوبون (بأمان)
            $pdo->prepare("UPDATE coupons SET used_count = used_count + 1 WHERE id = ?")->execute([$couponId]);
        }
    }

    $totalAmount = max(0, $subTotal + $shippingCost - $discountAmount);

    // 3. جلب بيانات العميل الممررة من الفورم
    $fullName = $_POST['full_name'] ?? 'عميل غير مسجل';
    $phone = $_POST['phone'] ?? '';
    $city = $_POST['city'] ?? '';
    $address = $_POST['address'] ?? '';
    $paymentMethod = $_POST['payment_method'] ?? 'CashOnDelivery';
    
    // توليد رقم طلب فريد وجميل
    $orderNumber = "ORD-" . date("ymd") . "-" . strtoupper(substr(uniqid(), -4));

    // 4. إنشاء الطلب في جدول orders
    $stmtOrder = $pdo->prepare("
        INSERT INTO orders 
        (order_number, user_id, sub_total, discount_amount, coupon_id, shipping_cost, total_amount, shipping_full_name, shipping_phone, shipping_city, shipping_address, payment_method, status) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Pending')
    ");
    
    $stmtOrder->execute([
        $orderNumber, $_SESSION['user_id'], $subTotal, $discountAmount, $couponId, 
        $shippingCost, $totalAmount, $fullName, $phone, $city, $address, $paymentMethod
    ]);
    
    $orderId = $pdo->lastInsertId();

    // حفظ سجل استخدام الكوبون للعميل
    if ($couponId) {
        $pdo->prepare("INSERT INTO coupon_usage (coupon_id, user_id, order_id, discount_applied) VALUES (?, ?, ?, ?)")
            ->execute([$couponId, $_SESSION['user_id'], $orderId, $discountAmount]);
    }

    // 5. حفظ عناصر الطلب وتحديث مخزون المنتجات
    $stmtItem = $pdo->prepare("INSERT INTO order_items (order_id, item_type, item_id, item_name, item_image, unit_price, quantity, total_price) VALUES (?, 'product', ?, ?, ?, ?, ?, ?)");
    $stmtUpdateStock = $pdo->prepare("UPDATE products SET stock_quantity = stock_quantity - ?, sales_count = sales_count + ? WHERE id = ?");

    foreach ($cartItems as $pid => $qty) {
        if (!isset($productsDict[$pid])) continue;
        
        $p = $productsDict[$pid];
        $actualQty = min($qty, $p['stock_quantity']);
        if ($actualQty <= 0) continue;

        $pTotal = $p['price'] * $actualQty;
        
        // إدخال تفاصيل الصنف
        $stmtItem->execute([$orderId, $pid, $p['name'], $p['image_url'], $p['price'], $actualQty, $pTotal]);
        
        // تقليل المخزون وزيادة عداد المبيعات للمنتج
        $stmtUpdateStock->execute([$actualQty, $actualQty, $pid]);
    }

    // 6. تصفير السلة وجلسة الكوبون بعد إتمام الطلب بنجاح
    unset($_SESSION['cart']);
    unset($_SESSION['coupon_code']);
    
    // توجيه لصفحة النجاح مع تمرير رقم الطلب
    header("Location: ../index.php?page=success&order=" . $orderNumber);
    exit;

} catch (PDOException $e) {
    die("حدث خطأ في قاعدة البيانات أثناء معالجة الطلب: " . $e->getMessage());
}
?>