<?php
// مسار الملف: pages/success.php
// المكان: داخل مجلد pages

$orderNumber = isset($_GET['order']) ? htmlspecialchars($_GET['order']) : '';

if (empty($orderNumber)) {
    echo "<script>window.location.href='index.php';</script>";
    exit;
}
?>

<div class="max-w-3xl mx-auto px-4 py-16 mb-14 text-center afiu">
    <div class="w-24 h-24 bg-green-50 rounded-full flex items-center justify-center mx-auto mb-6">
        <i class="fas fa-check-circle text-6xl text-green-500 animate-pulse"></i>
    </div>
    
    <h1 class="text-3xl sm:text-4xl font-black text-pri-900 font-amiri mb-4">شكراً لك، تم استلام طلبك بنجاح!</h1>
    <p class="text-brk-500 text-lg mb-8">نقدر ثقتك في متجر تشافي. سيتم تجهيز طلبك في أسرع وقت ممكن.</p>

    <div class="erp-card p-8 mb-8 text-right relative overflow-hidden">
        <div class="absolute top-0 right-0 w-2 h-full bg-gld-500"></div>
        <h3 class="text-xl font-bold text-pri-900 mb-6 border-b border-gray-100 pb-4">معلومات سريعة</h3>
        
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 text-sm">
            <div>
                <p class="text-brk-400 mb-1">رقم الطلب:</p>
                <p class="font-black text-pri-900 text-lg" dir="ltr"><?= $orderNumber ?></p>
            </div>
            <div>
                <p class="text-brk-400 mb-1">حالة الطلب:</p>
                <p class="font-bold text-gld-600 bg-gld-50 inline-block px-3 py-1 rounded-lg">قيد المراجعة والتجهيز</p>
            </div>
        </div>
    </div>

    <div class="flex flex-wrap items-center justify-center gap-4">
        <a href="index.php?page=dashboard" class="btn btn-primary btn-lg">
            <i class="fas fa-box-open"></i> تتبع الطلب
        </a>
        <a href="index.php?page=home" class="btn btn-outline btn-lg">
            <i class="fas fa-home"></i> العودة للرئيسية
        </a>
    </div>
</div>