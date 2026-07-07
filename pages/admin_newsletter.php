<?php
// مسار الملف: pages/admin_newsletter.php

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['Admin', 'SuperAdmin'])) {
    echo "<script>window.location.href='index.php';</script>"; exit;
}

// محاكاة جلب المشتركين (يمكنك إنشاء جدول newsletter_subscriptions إذا أردت)
$subscribers = [];
try {
    $subscribers = $pdo->query("SELECT email, created_at as subscribed_at, is_active FROM users ORDER BY created_at DESC LIMIT 10")->fetchAll();
} catch(Exception $e) {}
?>

<div class="max-w-7xl mx-auto px-4 py-8 mb-14 afiu">
    <div class="flex items-center justify-between mb-8">
        <h1 class="text-2xl font-black text-pri-900 font-amiri"><i class="fas fa-users text-gld-500 ml-2"></i>المشتركون في النشرة</h1>
        <a href="index.php?page=admin_dashboard" class="cf-btn cf-btn-out cf-btn-sm bg-white"><i class="fas fa-arrow-right"></i> لوحة القيادة</a>
    </div>

    <div class="erp-card overflow-hidden shadow-sm">
        <div class="table-responsive">
            <table class="erp-table">
                <thead>
                    <tr>
                        <th>البريد الإلكتروني</th>
                        <th class="text-center">تاريخ الاشتراك</th>
                        <th class="text-center">الحالة</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($subscribers)): ?>
                        <tr><td colspan="3" class="text-center py-10 text-brk-400">لا يوجد مشتركون حتى الآن</td></tr>
                    <?php else: ?>
                        <?php foreach ($subscribers as $item): ?>
                            <tr class="border-b border-gray-50 hover:bg-gray-50 transition">
                                <td class="font-bold text-pri-900" dir="ltr"><?= htmlspecialchars($item['email']) ?></td>
                                <td class="text-center text-sm text-brk-500"><?= date('Y-m-d H:i', strtotime($item['subscribed_at'])) ?></td>
                                <td class="text-center">
                                    <?php if ($item['is_active']): ?> <span class="badge badge-success">نشط</span>
                                    <?php else: ?> <span class="badge badge-danger">ملغى</span> <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>