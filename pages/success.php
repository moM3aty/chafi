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
    <div class="w-24 h-24 bg-green-50 rounded-full flex items-center justify-center mx-auto mb-6 shadow-sm border border-green-100">
        <i class="fas fa-check-circle text-6xl text-green-500 animate-pulse"></i>
    </div>
    
    <h1 class="text-3xl sm:text-4xl font-black text-pri-900 font-amiri mb-4">شكراً لك، تم استلام طلبك بنجاح!</h1>
    
    <!-- الرسالة المطلوبة بعد رفع التحويل البنكي -->
    <div class="bg-pri-50 text-pri-800 font-bold p-4 rounded-xl border-2 border-pri-200 inline-block mx-auto mb-8 shadow-sm">
        <i class="fas fa-clock ml-2 text-pri-600"></i> سوف يتم مراجعة الطلب والإيصال البنكي في خلال 48 ساعة
    </div>

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
                <p class="font-bold text-yellow-600 bg-yellow-50 border border-yellow-200 inline-block px-3 py-1 rounded-lg">قيد المراجعة</p>
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