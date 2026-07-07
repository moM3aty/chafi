<?php
// مسار الملف: pages/admin_settings.php

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['Admin', 'SuperAdmin'])) {
    echo "<script>window.location.href='index.php';</script>"; exit;
}

$settings = [];
try {
    $settings = $pdo->query("SELECT * FROM settings ORDER BY setting_group, id")->fetchAll();
} catch(Exception $e) {}
?>

<div class="max-w-7xl mx-auto px-4 py-8 mb-14 afiu">
    <div class="flex items-center justify-between mb-8 gap-4">
        <h1 class="text-2xl font-black text-pri-900 font-amiri"><i class="fas fa-cogs text-gld-500 ml-2"></i>إعدادات الموقع</h1>
        <?php if ($_SESSION['user_role'] == 'SuperAdmin'): ?>
            <a href="index.php?page=admin_roles" class="cf-btn cf-btn-pri cf-btn-sm text-xs">
                <i class="fas fa-user-shield"></i> إدارة الصلاحيات للمستخدمين
            </a>
        <?php endif; ?>
    </div>

    <div class="erp-card overflow-hidden shadow-sm">
        <div class="table-responsive">
            <table class="erp-table">
                <thead>
                    <tr>
                        <th>المفتاح (Key) / الوصف</th>
                        <th>القيمة (Value)</th>
                        <th class="text-center">إجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($settings)): ?>
                        <tr><td colspan="3" class="text-center py-10 text-brk-400">لم يتم إضافة إعدادات للموقع حتى الآن</td></tr>
                    <?php else: ?>
                        <?php foreach ($settings as $item): ?>
                            <tr class="border-b border-gray-50 hover:bg-gray-50 transition">
                                <td class="font-bold text-pri-900">
                                    <div dir="ltr" class="text-sm"><?= htmlspecialchars($item['setting_key']) ?></div>
                                    <div class="text-[11px] text-brk-400 font-normal mt-1"><?= htmlspecialchars($item['label_ar'] ?? '') ?></div>
                                </td>
                                <td class="text-pri-700 text-sm max-w-xs truncate" dir="ltr"><?= htmlspecialchars($item['setting_value']) ?></td>
                                <td class="flex justify-center gap-2">
                                    <a href="index.php?page=admin_settings_form" class="btn btn-sm btn-outline !py-1 !px-2 text-xs" title="تعديل"><i class="fas fa-edit"></i></a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>