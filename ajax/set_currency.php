<?php
// مسار الملف: ajax/set_currency.php
// الوظيفة: حفظ العملة المختارة في الجلسة ليتم استخدامها في تحويل الأسعار

session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['currency'])) {
    $currency = trim($_POST['currency']);
    
    // قائمة العملات المدعومة في النظام (تم تحديثها)
    $allowed = ['SAR', 'AED', 'KWD', 'BHD', 'OMR', 'QAR', 'EGP', 'JOD', 'MAD', 'DZD', 'TND', 'LBP', 'IQD', 'TRY', 'USD', 'EUR', 'GBP', 'CAD', 'AUD', 'CHF'];
    
    if (in_array($currency, $allowed)) {
        $_SESSION['currency'] = $currency;
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'العملة غير مدعومة']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'طلب غير صالح']);
}
?>