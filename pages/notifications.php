<?php
// مسار الملف: pages/notifications.php

if (!isset($_SESSION['user_id'])) {
    echo "<script>window.location.href='index.php';</script>"; exit;
}

$userId = $_SESSION['user_id'];

// تعليم كل الإشعارات كمقروءة
$pdo->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ? AND is_read = 0")->execute([$userId]);

$notifications = $pdo->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 50");
$notifications->execute([$userId]);
$notifs = $notifications->fetchAll();
?>

<div class="max-w-3xl mx-auto px-4 py-10 mb-14">
    <div class="flex items-center gap-3 mb-8 afiu">
        <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-pri-500 to-pri-800 flex items-center justify-center text-gld-400 text-xl shadow-lg">
            <i class="fas fa-bell"></i>
        </div>
        <h1 class="text-3xl font-black text-pri-900 font-amiri">الإشعارات</h1>
    </div>

    <?php if(empty($notifs)): ?>
        <div class="erp-card p-12 text-center afiu" style="animation-delay:.1s">
            <i class="fas fa-bell-slash text-5xl text-brk-200 mb-4"></i>
            <h3 class="text-xl font-bold text-pri-900 mb-2">لا توجد إشعارات</h3>
            <p class="text-brk-400">ستظهر هنا جميع إشعاراتك عند توفر جديد.</p>
        </div>
    <?php else: ?>
        <div class="space-y-3 afiu" style="animation-delay:.1s">
            <?php foreach($notifs as $n): ?>
                <div class="erp-card p-5 flex items-start gap-4 hover:border-pri-200 transition <?= !$n['is_read'] ? 'border-r-4 border-r-pri-500 bg-pri-50/30' : '' ?>">
                    <div class="w-10 h-10 rounded-full flex items-center justify-center shrink-0 text-lg
                        <?= $n['type'] == 'order' ? 'bg-blue-50 text-blue-600' : ($n['type'] == 'promo' ? 'bg-gld-50 text-gld-600' : 'bg-gray-50 text-brk-500') ?>">
                        <i class="fas <?= $n['type'] == 'order' ? 'fa-box' : ($n['type'] == 'promo' ? 'fa-tag' : 'fa-info-circle') ?>"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <h4 class="font-bold text-pri-900 text-sm mb-1"><?= htmlspecialchars($n['title']) ?></h4>
                        <?php if($n['body']): ?><p class="text-brk-500 text-xs leading-relaxed"><?= htmlspecialchars($n['body']) ?></p><?php endif; ?>
                        <div class="text-[10px] text-brk-300 mt-2" dir="ltr"><?= date('Y-m-d H:i', strtotime($n['created_at'])) ?></div>
                    </div>
                    <?php if($n['link_url']): ?>
                        <a href="<?= htmlspecialchars($n['link_url']) ?>" class="btn btn-sm btn-outline !py-1 !px-3 text-xs shrink-0">عرض</a>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>