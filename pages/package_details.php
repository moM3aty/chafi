<?php
// مسار الملف: pages/package_details.php

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// جلب تفاصيل الباقة من الداتابيز
$stmt = $pdo->prepare("SELECT * FROM packages WHERE id = ? AND is_active = 1");
$stmt->execute([$id]);
$package = $stmt->fetch();

// إذا لم يتم العثور على الباقة، نظهر رسالة خطأ 404 مهذبة
if (!$package) {
    echo "<div class='max-w-3xl mx-auto px-4 py-20 text-center afiu'>
            <div class='w-24 h-24 bg-red-50 text-red-400 rounded-full flex items-center justify-center mx-auto mb-6 text-5xl'><i class='fas fa-box-open'></i></div>
            <h1 class='text-3xl font-bold text-pri-900 mb-2'>عذراً، الباقة غير موجودة</h1>
            <p class='text-brk-500 mb-6'>قد تكون هذه الباقة قد تم إخفاؤها أو إزالتها من المتجر.</p>
            <a href='index.php?page=packages' class='btn btn-primary'><i class='fas fa-arrow-right'></i> العودة للباقات</a>
          </div>"; 
    return;
}

// حساب التوفير والنسبة المئوية
$savingsAmount = $package['original_total_price'] - $package['package_price'];
$savingsPercent = $package['original_total_price'] > 0 ? round(($savingsAmount / $package['original_total_price']) * 100) : 0;
?>

<div class="max-w-7xl mx-auto px-4 py-8 mb-14">
    <!-- مسار التنقل Breadcrumbs -->
    <div class="flex items-center gap-2 text-sm text-brk-400 mb-8 afiu">
        <a href="index.php" class="hover:text-pri-600 transition"><i class="fas fa-home"></i> الرئيسية</a>
        <i class="fas fa-chevron-left text-[10px]"></i>
        <a href="index.php?page=packages" class="hover:text-pri-600 transition">الباقات المميزة</a>
        <i class="fas fa-chevron-left text-[10px]"></i>
        <span class="text-pri-900 font-bold truncate max-w-[200px]"><?= htmlspecialchars($package['name']) ?></span>
    </div>

    <!-- بطاقة الباقة -->
    <div class="bg-white rounded-3xl border-2 border-border p-6 sm:p-10 shadow-sm relative overflow-hidden afiu" style="animation-delay: 0.1s">
        <?php if ($package['is_featured']): ?>
            <div class="absolute top-8 -right-12 bg-gld-500 text-white font-bold text-sm py-1.5 px-12 transform rotate-45 shadow-md z-10">الأكثر طلباً</div>
        <?php endif; ?>

        <div class="flex flex-col lg:flex-row gap-10">
            <!-- الصورة -->
            <div class="w-full lg:w-5/12 shrink-0">
                <div class="rounded-2xl overflow-hidden bg-gray-50 border border-gray-100 aspect-square relative shadow-inner">
                    <img src="<?= htmlspecialchars($package['image_url'] ?? 'https://picsum.photos/600') ?>" alt="<?= htmlspecialchars($package['name']) ?>" class="w-full h-full object-cover transition-transform duration-500 hover:scale-105">
                </div>
            </div>

            <!-- التفاصيل والوصف -->
            <div class="w-full lg:w-7/12 flex flex-col">
                <h1 class="text-3xl sm:text-4xl font-black text-pri-900 font-amiri mb-3 leading-tight"><?= htmlspecialchars($package['name']) ?></h1>
                
                <div class="flex items-center gap-3 mb-6">
                    <span class="pkg-save bg-red-500 text-white px-3 py-1 rounded-full text-xs font-bold shadow-sm"><i class="fas fa-tags ml-1"></i> توفير <?= $savingsPercent ?>%</span>
                    <span class="text-brk-400 text-sm"><i class="fas fa-shopping-bag ml-1 text-pri-400"></i> تم الشراء <?= $package['sales_count'] ?> مرة</span>
                </div>

                <!-- الأسعار (مدمجة ليعمل محول العملات) -->
                <div class="bg-gradient-to-r from-pri-50 to-white rounded-2xl p-6 mb-8 border border-pri-100 flex items-center justify-between shadow-sm">
                    <div>
                        <div class="text-brk-500 text-sm line-through mb-1">السعر الأصلي: <?= number_format($package['original_total_price'], 2) ?> ر.س</div>
                        <!-- تم دمج الرقم مع ر.س ليعمل سكربت التحويل بسلاسة -->
                        <div class="text-4xl font-black text-pri-700"><?= number_format($package['package_price'], 2) ?> ر.س</div>
                    </div>
                    <div class="text-center bg-white p-3 rounded-xl border border-pri-100 shadow-sm hidden sm:block">
                        <div class="text-xs text-brk-400 mb-1">مقدار التوفير</div>
                        <div class="text-lg font-black text-green-600"><?= number_format($savingsAmount, 2) ?> ر.س</div>
                    </div>
                </div>

                <!-- الوصف (HTML معتمد) -->
                <!-- ملاحظة: لا نستخدم htmlspecialchars هنا لأننا نريد عرض التنسيقات والألوان التي كتبها المدير في CKEditor -->
                <div class="prose prose-sm max-w-none text-brk-600 leading-loose mb-8 custom-html-content">
                    <?= $package['description'] ?>
                </div>

                <!-- زر الإضافة للسلة -->
                <div class="mt-auto pt-6 border-t border-gray-100">
                    <div class="flex flex-wrap items-center gap-4">
                        <div class="qty bg-gray-50 border-2 border-border h-14 rounded-xl flex items-center">
                            <button type="button" class="qty-b w-12 h-full text-xl hover:bg-gray-200 transition" onclick="decQty()">-</button>
                            <input type="number" id="pkgQty" value="1" min="1" max="100" class="qty-v w-12 bg-transparent text-lg font-black text-pri-900 border-0 text-center" readonly>
                            <button type="button" class="qty-b w-12 h-full text-xl hover:bg-gray-200 transition" onclick="incQty()">+</button>
                        </div>
                        
                        <!-- استخدام دالة السلة الذكية الجديدة: addToCart('نوع العنصر', ID, الكمية) -->
                        <button onclick="addToCart('package', <?= $package['id'] ?>, document.getElementById('pkgQty').value)" class="btn btn-gold flex-1 h-14 text-lg shadow-xl hover:scale-[1.02] transition-transform">
                            <i class="fas fa-cart-plus"></i> إضافة الباقة للسلة
                        </button>
                    </div>
                    <p class="text-xs text-center text-brk-400 mt-4"><i class="fas fa-shield-alt text-gld-500"></i> دفع آمن ومشفر 100%</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // زيادة الكمية
    function incQty() { 
        let input = document.getElementById('pkgQty'); 
        if (input && input.value < 100) {
            input.value = parseInt(input.value) + 1; 
        }
    }
    
    // إنقاص الكمية
    function decQty() { 
        let input = document.getElementById('pkgQty'); 
        if (input && input.value > 1) {
            input.value = parseInt(input.value) - 1; 
        }
    }
</script>