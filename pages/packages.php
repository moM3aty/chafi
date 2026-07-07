<?php
// مسار الملف: pages/packages.php

$packages = $pdo->query("SELECT * FROM packages WHERE is_active = 1 ORDER BY is_featured DESC, id DESC")->fetchAll();
?>

<div class="max-w-7xl mx-auto px-4 py-12 mb-14">
    <div class="text-center mb-12 afiu">
        <h1 class="text-3xl sm:text-4xl font-black text-pri-900 font-amiri mb-4">الباقات المميزة</h1>
        <div class="orn"><div class="orn-ln"></div><div class="orn-dm"></div><div class="orn-ln"></div></div>
        <p class="text-brk-500 text-base max-w-lg mx-auto">مجموعات متكاملة من منتجات الرقية الشرعية بأسعار مخفضة وتوفير كبير.</p>
    </div>

    <?php if (empty($packages)): ?>
        <div class="bg-white rounded-3xl border-2 border-border p-12 text-center shadow-sm afiu" style="animation-delay: 0.2s">
            <i class="fas fa-boxes text-6xl text-brk-300 mb-4 opacity-50"></i>
            <h3 class="text-xl font-bold text-pri-900 mb-2">لا توجد باقات حالياً</h3>
            <p class="text-brk-400">نعمل على تجهيز باقات جديدة قريباً، تفضل بزيارة هذه الصفحة لاحقاً.</p>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <?php foreach ($packages as $p): 
                $savings = $p['original_total_price'] > 0 ? round((($p['original_total_price'] - $p['package_price']) / $p['original_total_price']) * 100) : 0;
            ?>
                <div class="pkg-card <?= $p['is_featured'] ? 'feat' : '' ?> afiu">
                    <?php if ($p['is_featured']): ?>
                        <div class="pkg-feat-badge">الأكثر طلباً</div>
                    <?php endif; ?>
                    <div class="pkg-hd">
                        <img src="<?= htmlspecialchars($p['image_url'] ?? "https://picsum.photos/400") ?>" alt="<?= htmlspecialchars($p['name']) ?>" class="w-24 h-24 rounded-2xl mx-auto mb-4 object-cover <?= !$p['is_featured'] ? 'border-2 border-gray-100' : '' ?>" loading="lazy">
                        <h3 class="text-xl font-black <?= $p['is_featured'] ? 'text-white' : 'text-pri-900' ?>"><?= htmlspecialchars($p['name']) ?></h3>
                    </div>
                    <div class="p-6">
                        <div class="flex items-center justify-center gap-3 mb-4">
                            <span class="pkg-save"><i class="fas fa-bolt text-[10px]"></i> وفّر <?= $savings ?>%</span>
                            <span class="line-through text-brk-300 text-sm"><?= number_format($p['original_total_price'], 2) ?> ر.س</span>
                        </div>
                        <div class="text-center mb-6">
                            <span class="text-4xl font-black text-pri-700"><?= number_format($p['package_price'], 2) ?></span>
                            <span class="text-brk-400 text-sm mr-1">ر.س</span>
                        </div>
                        <a href="index.php?page=package_details&id=<?= $p['id'] ?>" class="btn <?= $p['is_featured'] ? 'btn-gold' : 'btn-primary' ?> btn-block">
                            تفاصيل الباقة <i class="fas fa-arrow-left text-xs mr-1"></i>
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>