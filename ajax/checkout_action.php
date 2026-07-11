<?php
// مسار الملف: ajax/checkout_action.php
// الوظيفة: معالجة الطلب النهائي مع احتساب الكوبونات ورفع الإيصال البنكي

session_start();
require_once '../config.php';

if (!isset($_SESSION['user_id']) || empty($_SESSION['cart'])) {
    header("Location: ../index.php");
    exit;
}

try {
    $cartItems = $_SESSION['cart'];
    $subTotal = 0;
    $hasPhysicalItems = false;
    $finalItems = [];
    
    // حساب المجاميع من الجداول المختلفة لحماية البيانات
    foreach ($cartItems as $key => $item) {
        $table = 'products'; $nameCol = 'name'; $priceCol = 'price'; $imgCol = 'image_url';
        if ($item['type'] === 'audio') { $table = 'audios'; $nameCol = 'title'; $imgCol = 'thumbnail_url'; }
        elseif ($item['type'] === 'video') { $table = 'videos'; $nameCol = 'title'; $imgCol = 'thumbnail_url'; }
        elseif ($item['type'] === 'package') { $table = 'packages'; $priceCol = 'package_price'; }
        
        $query = "SELECT id, $nameCol as name, $priceCol as price, $imgCol as image";
        if ($item['type'] === 'product') $query .= ", stock_quantity, is_digital";
        $query .= " FROM $table WHERE id = ?";
        
        $stmt = $pdo->prepare($query);
        $stmt->execute([$item['id']]);
        $dbItem = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($dbItem) {
            $qty = $item['qty'];
            if ($item['type'] === 'product' && $dbItem['is_digital'] == 0) {
                $qty = min($qty, $dbItem['stock_quantity']);
                $hasPhysicalItems = true;
            }
            if ($qty <= 0) continue;
            
            $subTotal += $dbItem['price'] * $qty;
            $finalItems[] = [
                'type' => $item['type'],
                'id' => $dbItem['id'],
                'name' => $dbItem['name'],
                'image' => $dbItem['image'],
                'price' => $dbItem['price'],
                'qty' => $qty
            ];
        }
    }

    $shippingCost = ($hasPhysicalItems && $subTotal > 0 && $subTotal < 200) ? 25 : 0;
    
    // التحقق من الكوبون
    $couponId = null;
    $discountAmount = 0;
    if (isset($_SESSION['coupon_code']) && $subTotal > 0) {
        $cStmt = $pdo->prepare("SELECT id, discount_type, discount_value, max_discount, min_order_amount FROM coupons WHERE code = ? AND is_active = 1");
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
            $pdo->prepare("UPDATE coupons SET used_count = used_count + 1 WHERE id = ?")->execute([$couponId]);
        }
    }

    $totalAmount = max(0, $subTotal + $shippingCost - $discountAmount);

    $fullName = $_POST['full_name'] ?? 'عميل غير مسجل';
    $phone = $_POST['phone'] ?? '';
    $city = $_POST['city'] ?? '';
    $address = $_POST['address'] ?? '';
    $paymentMethod = $_POST['payment_method'] ?? 'BankTransfer';
    $orderNumber = "ORD-" . date("ymd") . "-" . strtoupper(substr(uniqid(), -4));

    // --- معالجة رفع صورة إيصال التحويل ---
    $receiptUrl = '';
    if (isset($_FILES['transfer_receipt']) && $_FILES['transfer_receipt']['error'] == 0) {
        $uploadDir = '../assets/uploads/receipts/';
        if (!is_dir($uploadDir)) { mkdir($uploadDir, 0777, true); }
        
        // تنظيف اسم الملف
        $fileName = time() . '_' . preg_replace('/[^a-zA-Z0-9.\-_]/', '', basename($_FILES['transfer_receipt']['name']));
        $targetFile = $uploadDir . $fileName;
        
        if (move_uploaded_file($_FILES['transfer_receipt']['tmp_name'], $targetFile)) {
            // حفظ المسار النسبي لقاعدة البيانات
            $receiptUrl = 'assets/uploads/receipts/' . $fileName;
        } else {
            die("<script>alert('حدث خطأ أثناء رفع صورة الإيصال. يرجى المحاولة مرة أخرى.'); history.back();</script>");
        }
    } else {
        die("<script>alert('صورة إيصال التحويل البنكي مطلوبة لإتمام الطلب.'); history.back();</script>");
    }
    // --------------------------------------

    // إنشاء الطلب الرئيسي (مع حفظ مسار الإيصال)
    // لاحظ: إذا لم يكن حقل transfer_receipt_url موجوداً في الـ Database، سيقوم setup_db بإنشائه لاحقاً بفضل الحماية التي أضفناها، ولكننا نرسله هنا.
    $stmtOrder = $pdo->prepare("
        INSERT INTO orders 
        (order_number, user_id, sub_total, discount_amount, coupon_id, shipping_cost, total_amount, shipping_full_name, shipping_phone, shipping_city, shipping_address, payment_method, transfer_receipt_url, status) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Pending')
    ");
    $stmtOrder->execute([$orderNumber, $_SESSION['user_id'], $subTotal, $discountAmount, $couponId, $shippingCost, $totalAmount, $fullName, $phone, $city, $address, $paymentMethod, $receiptUrl]);
    $orderId = $pdo->lastInsertId();

    if ($couponId) {
        $pdo->prepare("INSERT INTO coupon_usage (coupon_id, user_id, order_id, discount_applied) VALUES (?, ?, ?, ?)")->execute([$couponId, $_SESSION['user_id'], $orderId, $discountAmount]);
    }

    // حفظ عناصر الطلب وتحديث المخزون والإحصائيات
    $stmtItem = $pdo->prepare("INSERT INTO order_items (order_id, item_type, item_id, item_name, item_image, unit_price, quantity, total_price) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmtUpdateStock = $pdo->prepare("UPDATE products SET stock_quantity = stock_quantity - ?, sales_count = sales_count + ? WHERE id = ?");
    $stmtUpdateAudioSales = $pdo->prepare("UPDATE audios SET download_count = download_count + ? WHERE id = ?");
    $stmtUpdateVideoSales = $pdo->prepare("UPDATE videos SET view_count = view_count + ? WHERE id = ?");
    
    foreach ($finalItems as $fi) {
        $pTotal = $fi['price'] * $fi['qty'];
        $stmtItem->execute([$orderId, $fi['type'], $fi['id'], $fi['name'], $fi['image'], $fi['price'], $fi['qty'], $pTotal]);
        
        // تحديث المخزون والإحصائيات بناءً على النوع
        if ($fi['type'] === 'product') {
            $stmtUpdateStock->execute([$fi['qty'], $fi['qty'], $fi['id']]);
        } elseif ($fi['type'] === 'audio') {
            $stmtUpdateAudioSales->execute([$fi['qty'], $fi['id']]);
        } elseif ($fi['type'] === 'video') {
            $stmtUpdateVideoSales->execute([$fi['qty'], $fi['id']]);
        }
    }

    // تفريغ السلة والكوبون بعد إتمام الطلب بنجاح
    unset($_SESSION['cart']);
    unset($_SESSION['coupon_code']);
    
    header("Location: ../index.php?page=success&order=" . $orderNumber);
    exit;

} catch (PDOException $e) {
    die("حدث خطأ في قاعدة البيانات أثناء معالجة الطلب: " . $e->getMessage());
}
?>