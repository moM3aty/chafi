<?php
// مسار الملف: pages/package_details.php

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$stmt = $pdo->prepare("SELECT * FROM packages WHERE id = ? AND is_active = 1");
$stmt->execute([$id]);
$package = $stmt->fetch();

if (!$package) {
    echo "<div class='text-center py-20'><h1 class='text-3xl font-bold'>الباقة غير موجودة</h1></div>"; return;
}

// جلب المنتجات داخل هذه الباقة
$stmtItems = $pdo->prepare("SELECT p.*, pi.quantity FROM package_items pi JOIN products p ON pi.item_id = p.id WHERE pi.package_id = ? AND pi.item_type = 'product'");
$stmtItems->execute([$id]);
$items = $stmtItems->fetchAll();

$savingsAmount = $package['original_total_price'] - $package['package_price'];
$savingsPercent = $package['original_total_price'] > 0 ? round(($savingsAmount / $package['original_total_price']) * 100) : 0;
?>

<div class="max-w-7xl mx-auto px-4 py-8 mb-14">
    <div class="flex items-center gap-2 text-sm text-brk-400 mb-8">
        <a href="index.php" class="hover:text-pri-600 transition"><i class="fas fa-home"></i> الرئيسية</a>
        <i class="fas fa-chevron-left text-[10px]"></i>
        <a href="index.php?page=packages" class="hover:text-pri-600 transition">الباقات المميزة</a>
        <i class="fas fa-chevron-left text-[10px]"></i>
        <span class="text-pri-900 font-bold truncate"><?= htmlspecialchars($package['name']) ?></span>
    </div>

    <div class="bg-white rounded-3xl border-2 border-border p-6 sm:p-10 shadow-sm relative overflow-hidden">
        <?php if ($package['is_featured']): ?>
            <div class="absolute top-8 -right-12 bg-gld-500 text-white font-bold text-sm py-1.5 px-12 transform rotate-45 shadow-md">باقة مميزة</div>
        <?php endif; ?>

        <div class="flex flex-col lg:flex-row gap-10">
            <div class="w-full lg:w-5/12 shrink-0">
                <div class="rounded-2xl overflow-hidden bg-gray-50 border border-gray-100 aspect-square relative mb-6">
                    <img src="<?= htmlspecialchars($package['image_url'] ?? 'https://picsum.photos/600') ?>" class="w-full h-full object-cover">
                </div>
                
                <h3 class="text-lg font-black text-pri-900 mb-4 border-b border-gray-100 pb-2">محتويات الباقة:</h3>
                <ul class="space-y-3">
                    <?php foreach ($items as $item): ?>
                        <li class="flex items-center gap-3 p-3 rounded-xl bg-gray-50 border border-gray-100">
                            <img src="<?= htmlspecialchars($item['image_url']) ?>" class="w-10 h-10 rounded object-cover">
                            <div class="flex-1 min-w-0 text-sm">
                                <div class="font-bold text-pri-900 truncate"><?= htmlspecialchars($item['name']) ?></div>
                                <div class="text-brk-400 text-xs">الكمية: <?= $item['quantity'] ?></div>
                            </div>
                        </li>
                    <?php endforeach; ?>
                    <?php if(empty($items)): ?>
                        <li class="text-brk-400 text-sm">لم يتم إضافة منتجات لهذه الباقة بعد.</li>
                    <?php endif; ?>
                </ul>
            </div>

            <div class="w-full lg:w-7/12 flex flex-col">
                <h1 class="text-3xl sm:text-4xl font-black text-pri-900 font-amiri mb-3"><?= htmlspecialchars($package['name']) ?></h1>
                
                <div class="flex items-center gap-3 mb-6">
                    <span class="pkg-save bg-red-500 text-white px-3 py-1 rounded-full text-xs font-bold"><i class="fas fa-tags ml-1"></i> توفير <?= $savingsPercent ?>%</span>
                    <span class="text-brk-400 text-sm"><i class="fas fa-shopping-bag ml-1"></i> <?= $package['sales_count'] ?> مبيع</span>
                </div>

                <div class="bg-pri-50 rounded-2xl p-6 mb-8 border border-pri-100 flex items-center justify-between">
                    <div>
                        <div class="text-brk-500 text-sm line-through mb-1">السعر الأصلي: <?= number_format($package['original_total_price'], 2) ?> ر.س</div>
                        <div class="text-4xl font-black text-pri-700"><?= number_format($package['package_price'], 2) ?> <span class="text-lg font-bold">ر.س</span></div>
                    </div>
                    <div class="text-center bg-white p-3 rounded-xl border border-pri-100 shadow-sm hidden sm:block">
                        <div class="text-xs text-brk-400 mb-1">مقدار التوفير</div>
                        <div class="text-lg font-black text-green-600"><?= number_format($savingsAmount, 2) ?> ر.س</div>
                    </div>
                </div>

                <div class="prose prose-sm text-brk-500 leading-relaxed mb-8">
                    <?= nl2br(htmlspecialchars($package['description'])) ?>
                </div>

                <div class="mt-auto">
                    <div class="flex flex-wrap gap-4">
                        <div class="qty bg-gray-50 border-2 border-border h-14 rounded-xl">
                            <button type="button" class="qty-b w-12 h-full text-xl" onclick="decQty()">-</button>
                            <input type="number" id="pkgQty" value="1" min="1" class="qty-v w-12 bg-transparent text-lg border-0 text-center" readonly>
                            <button type="button" class="qty-b w-12 h-full text-xl" onclick="incQty()">+</button>
                        </div>
                        <button onclick="addToCart(0, document.getElementById('pkgQty').value, <?= $package['id'] ?>)" class="btn btn-gold flex-1 h-14 text-lg shadow-lg">
                            <i class="fas fa-cart-plus"></i> إضافة الباقة للسلة
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    function incQty() { document.getElementById('pkgQty').value++; }
    function decQty() { let i = document.getElementById('pkgQty'); if(i.value > 1) i.value--; }
</script>