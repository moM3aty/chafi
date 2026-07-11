<?php
// مسار الملف: ajax/cart_action.php
session_start();
require_once '../config.php';

// تنظيف أي مخرجات سابقة لضمان عودة JSON سليم 100% لتجنب خطأ "حدث خطأ في الاتصال"
if (ob_get_length()) ob_clean();
header('Content-Type: application/json');

$response = ['success' => false, 'message' => ''];

try {
    $action = isset($_POST['action']) ? $_POST['action'] : '';
    
    // التقاط نوع العنصر ورقمه (الأسلوب الجديد)
    $itemType = $_POST['item_type'] ?? ''; 
    $itemId = (int)($_POST['item_id'] ?? 0);
    $qty = (int)($_POST['quantity'] ?? 1);
    
    // التوافق الرجعي (Fallback) مع أكواد الـ JS القديمة في بعض الصفحات إن وجدت
    if (empty($itemType) || $itemId === 0) {
        if (isset($_POST['product_id'])) { $itemId = (int)$_POST['product_id']; $itemType = 'product'; }
        elseif (isset($_POST['audio_id'])) { $itemId = (int)$_POST['audio_id']; $itemType = 'audio'; }
        elseif (isset($_POST['video_id'])) { $itemId = (int)$_POST['video_id']; $itemType = 'video'; }
        elseif (isset($_POST['package_id'])) { $itemId = (int)$_POST['package_id']; $itemType = 'package'; }
    }

    if (empty($itemType)) $itemType = 'product'; // افتراضي
    
    $cartKey = $itemType . '_' . $itemId;

    if ($action === 'add') {
        if ($itemId > 0) {
            $stock = 99999; // المخزون الافتراضي للملفات الرقمية والصوتيات والفيديوهات
            
            // التحقق من المخزون للمنتجات الملموسة والتحقق مما إذا كان المنتج رقمي
            if ($itemType === 'product') {
                $stmt = $pdo->prepare("SELECT stock_quantity, is_digital FROM products WHERE id = ?");
                $stmt->execute([$itemId]);
                $pData = $stmt->fetch();
                if ($pData && $pData['is_digital'] == 0) {
                    $stock = $pData['stock_quantity'];
                }
            }

            if ($stock >= $qty) {
                // إذا كان منتج رقمي (صوتي، فيديو، باقة، أو منتج digital)، لا نضيفه مرتين لنفس المستخدم في السلة ولا نزيد الكمية
                if (in_array($itemType, ['audio', 'video', 'package']) || ($itemType === 'product' && isset($pData) && $pData['is_digital'] == 1)) {
                     $_SESSION['cart'][$cartKey] = [
                        'type' => $itemType,
                        'id' => $itemId,
                        'qty' => 1 // المنتجات الرقمية تُشترى مرة واحدة فقط
                    ];
                    $response['message'] = 'تمت الإضافة للسلة بنجاح (كمنتج رقمي مرة واحدة)';
                } else {
                    // للمنتجات الملموسة العادية
                    if (isset($_SESSION['cart'][$cartKey])) {
                        $newQty = $_SESSION['cart'][$cartKey]['qty'] + $qty;
                        $_SESSION['cart'][$cartKey]['qty'] = min($newQty, $stock);
                    } else {
                        $_SESSION['cart'][$cartKey] = [
                            'type' => $itemType,
                            'id' => $itemId,
                            'qty' => $qty
                        ];
                    }
                    $response['message'] = 'تمت الإضافة للسلة بنجاح';
                }
                
                $response['success'] = true;
            } else {
                $response['message'] = 'الكمية المطلوبة غير متوفرة في المخزون';
            }
        } else {
            $response['message'] = 'بيانات العنصر غير صحيحة';
        }
    } 
    elseif ($action === 'update') {
        if ($itemId > 0 && $qty > 0 && isset($_SESSION['cart'][$cartKey])) {
            $stock = 99999;
            if ($itemType === 'product') {
                $stmt = $pdo->prepare("SELECT stock_quantity, is_digital FROM products WHERE id = ?");
                $stmt->execute([$itemId]);
                $pData = $stmt->fetch();
                if ($pData && $pData['is_digital'] == 0) {
                    $stock = $pData['stock_quantity'];
                }
            }
            $_SESSION['cart'][$cartKey]['qty'] = min($qty, $stock);
            $response['success'] = true;
        }
    } 
    elseif ($action === 'remove') {
        if (isset($_SESSION['cart'][$cartKey])) {
            unset($_SESSION['cart'][$cartKey]);
            $response['success'] = true;
            $response['message'] = 'تم حذف العنصر من السلة';
        }
    } 
    
    elseif ($action === 'apply_coupon') {
        $code = strtoupper(trim($_POST['coupon_code'] ?? ''));
        
        $stmt = $pdo->prepare("SELECT * FROM coupons WHERE code = ? AND is_active = 1");
        $stmt->execute([$code]);
        $coupon = $stmt->fetch();
        
        if ($coupon) {
            $now = time();
            $starts = !empty($coupon['starts_at']) ? strtotime($coupon['starts_at']) : 0;
            $expires = !empty($coupon['expires_at']) ? strtotime($coupon['expires_at']) : 0;

            if ($starts > 0 && $now < $starts) {
                unset($_SESSION['coupon_code']);
                $response['message'] = 'عذراً، هذا الكوبون لم يبدأ وقت استخدامه بعد.';
            } elseif ($expires > 0 && $now > $expires) {
                unset($_SESSION['coupon_code']);
                $response['message'] = 'عذراً، هذا الكوبون منتهي الصلاحية.';
            } else {
                $_SESSION['coupon_code'] = $coupon['code'];
                $response['success'] = true;
                $response['message'] = 'تم تطبيق كود الخصم بنجاح!';
            }
        } else {
            unset($_SESSION['coupon_code']);
            $response['message'] = 'الكوبون غير صالح أو غير موجود.';
        }
    } 
    elseif ($action === 'remove_coupon') {
        unset($_SESSION['coupon_code']);
        $response['success'] = true;
        $response['message'] = 'تم إزالة الكوبون';
    }

    $subTotal = 0;
    $totalItems = 0;
    $itemLineTotal = 0;
    $hasPhysicalItems = false;
    
    if (isset($_SESSION['cart'])) {
        foreach ($_SESSION['cart'] as $key => $item) {
            if (!is_array($item)) continue;

            $totalItems += $item['qty'];
            $table = 'products';
            $priceCol = 'price';
            
            if ($item['type'] === 'audio') $table = 'audios';
            elseif ($item['type'] === 'video') $table = 'videos';
            elseif ($item['type'] === 'package') { $table = 'packages'; $priceCol = 'package_price'; }
            
            $stmt = $pdo->prepare("SELECT $priceCol as price " . ($item['type'] == 'product' ? ", is_digital" : "") . " FROM $table WHERE id = ?");
            $stmt->execute([$item['id']]);
            $data = $stmt->fetch();
            
            if ($data) {
                $lineTotal = $data['price'] * $item['qty'];
                $subTotal += $lineTotal;
                if ($key === $cartKey) {
                    $itemLineTotal = $lineTotal;
                }
                if ($item['type'] === 'product' && isset($data['is_digital']) && $data['is_digital'] == 0) {
                    $hasPhysicalItems = true;
                }
            }
        }
    }
    
    // الشحن يحسب فقط للمنتجات الملموسة
    $shippingCost = ($hasPhysicalItems && $subTotal > 0 && $subTotal < 200) ? 25 : 0;
    $couponDiscount = 0;
    
    if (isset($_SESSION['coupon_code']) && $subTotal > 0) {
        $stmt = $pdo->prepare("SELECT * FROM coupons WHERE code = ?");
        $stmt->execute([$_SESSION['coupon_code']]);
        $coupon = $stmt->fetch();
        
        if ($coupon) {
            if ($subTotal >= $coupon['min_order_amount']) {
                if ($coupon['discount_type'] === 'percentage') {
                    $couponDiscount = $subTotal * ($coupon['discount_value'] / 100);
                    if ($coupon['max_discount'] > 0 && $couponDiscount > $coupon['max_discount']) {
                        $couponDiscount = $coupon['max_discount'];
                    }
                } else {
                    $couponDiscount = $coupon['discount_value'];
                }
            } else {
                if ($action === 'apply_coupon') {
                    $response['success'] = false;
                    $response['message'] = "هذا الكوبون يتطلب مشتريات بقيمة " . $coupon['min_order_amount'] . " ر.س على الأقل.";
                    unset($_SESSION['coupon_code']);
                }
            }
        } else {
            unset($_SESSION['coupon_code']);
        }
    }
    
    if (empty($_SESSION['cart'])) {
        unset($_SESSION['coupon_code']);
        $couponDiscount = 0;
    }
    
    $total = max(0, $subTotal + $shippingCost - $couponDiscount);
    
    $response['item_key'] = $cartKey ?? '';
    $response['item_line_total'] = number_format($itemLineTotal, 2);
    $response['total_items'] = $totalItems;
    $response['totals'] = [
        'count' => $totalItems,
        'subTotal' => number_format($subTotal, 2),
        'shipping' => $shippingCost == 0 ? 'مجاني' : number_format($shippingCost, 2) . ' ر.س',
        'discount' => number_format($couponDiscount, 2),
        'total' => number_format($total, 2)
    ];
    
    echo json_encode($response);

} catch (\Throwable $e) {
    if (ob_get_length()) ob_clean();
    echo json_encode(['success' => false, 'message' => 'حدث خطأ غير متوقع: ' . $e->getMessage()]);
}
?>