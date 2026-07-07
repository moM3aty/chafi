<?php
// مسار الملف: pages/admin_users.php

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['Admin', 'SuperAdmin'])) {
    echo "<script>window.location.href='index.php';</script>"; exit;
}

// معالجة الحذف (لا يمكن حذف نفسك)
if (isset($_POST['delete_id'])) {
    $delId = (int)$_POST['delete_id'];
    if ($delId != $_SESSION['user_id']) {
        $pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$delId]);
    }
    echo "<script>window.location.href='index.php?page=admin_users&deleted=1';</script>"; exit;
}

// معالجة تفعيل/تعطيل
if (isset($_POST['toggle_active'])) {
    $tId = (int)$_POST['user_id'];
    $pdo->prepare("UPDATE users SET is_active = IF(is_active=1,0,1) WHERE id = ? AND id != ?")->execute([$tId, $_SESSION['user_id']]);
    echo "<script>window.location.href='index.php?page=admin_users';</script>"; exit;
}

$users = $pdo->query("SELECT u.*, (SELECT COUNT(*) FROM orders o WHERE o.user_id = u.id) as orders_count, (SELECT SUM(o.total_amount) FROM orders o WHERE o.user_id = u.id AND o.status NOT IN ('Cancelled','Refunded')) as total_spent FROM users u ORDER BY u.created_at DESC")->fetchAll();
?>

<div class="max-w-7xl mx-auto px-4 py-8 mb-14 afiu">
    <div class="flex flex-col sm:flex-row items-center justify-between mb-8 gap-4">
        <h1 class="text-2xl font-black text-pri-900 font-amiri"><i class="fas fa-users-cog text-gld-500 ml-2"></i>إدارة المستخدمين</h1>
        <div class="flex gap-2">
            <a href="index.php?page=admin_dashboard" class="cf-btn cf-btn-out cf-btn-sm bg-white"><i class="fas fa-arrow-right"></i> لوحة القيادة</a>
            <a href="index.php?page=admin_user_form" class="cf-btn cf-btn-pri cf-btn-sm"><i class="fas fa-user-plus"></i> مستخدم جديد</a>
            <a href="index.php?page=admin_roles" class="cf-btn cf-btn-out cf-btn-sm bg-white text-xs"><i class="fas fa-shield-alt"></i> الصلاحيات</a>
        </div>
    </div>

    <?php if(isset($_GET['deleted'])): ?>
        <div class="bg-green-50 border-r-4 border-green-500 p-4 rounded-xl text-green-700 font-bold mb-6">تم حذف المستخدم بنجاح.</div>
    <?php endif; ?>

    <div class="erp-card overflow-hidden">
        <div class="table-responsive">
            <table class="erp-table">
                <thead>
                    <tr>
                        <th>المستخدم</th>
                        <th>البريد</th>
                        <th>الصلاحية</th>
                        <th>الطلبات</th>
                        <th>إجمالي المشتريات</th>
                        <th>تاريخ التسجيل</th>
                        <th>الحالة</th>
                        <th class="text-center">إجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($users)): ?>
                        <tr><td colspan="8" class="text-center py-10 text-brk-400">لا يوجد مستخدمين</td></tr>
                    <?php else: ?>
                        <?php foreach($users as $u): ?>
                            <tr class="<?= !$u['is_active'] ? 'opacity-50' : '' ?>">
                                <td>
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-full bg-gradient-to-br from-pri-500 to-pri-800 flex items-center justify-center text-white text-sm font-bold shrink-0"><?= mb_substr($u['full_name'], 0, 1) ?></div>
                                        <div class="font-bold text-pri-900"><?= htmlspecialchars($u['full_name']) ?></div>
                                    </div>
                                </td>
                                <td class="text-sm text-brk-500" dir="ltr"><?= htmlspecialchars($u['email']) ?></td>
                                <td>
                                    <?php if ($u['role'] == 'SuperAdmin'): ?>
                                        <span class="bg-gld-100 text-gld-800 px-2 py-1 rounded text-[10px] font-bold">مدير عام</span>
                                    <?php elseif ($u['role'] == 'Admin'): ?>
                                        <span class="bg-pri-50 text-pri-700 px-2 py-1 rounded text-[10px] font-bold">مدير</span>
                                    <?php else: ?>
                                        <span class="bg-gray-100 text-gray-600 px-2 py-1 rounded text-[10px] font-bold">مستخدم</span>
                                    <?php endif; ?>
                                </td>
                                <td class="font-bold text-center"><?= $u['orders_count'] ?></td>
                                <td class="font-bold text-pri-700"><?= number_format($u['total_spent'] ?: 0, 0) ?> ر.س</td>
                                <td class="text-xs text-brk-400" dir="ltr"><?= date('Y-m-d', strtotime($u['created_at'])) ?></td>
                                <td>
                                    <?php if($u['is_active']): ?> <span class="badge badge-success">نشط</span> <?php else: ?> <span class="badge badge-danger">معطل</span> <?php endif; ?>
                                </td>
                                <td class="flex justify-center gap-1">
                                    <a href="index.php?page=admin_user_form&id=<?= $u['id'] ?>" class="btn btn-sm btn-outline !py-1 !px-2 text-xs" title="تعديل"><i class="fas fa-edit"></i></a>
                                    <?php if($u['id'] != $_SESSION['user_id']): ?>
                                        <form method="post" onsubmit="return confirm('تأكيد <?= $u['is_active'] ? 'تعطيل' : 'تفعيل' ?> المستخدم؟');">
                                            <input type="hidden" name="toggle_active" value="1">
                                            <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                            <button type="submit" class="btn btn-sm btn-outline !py-1 !px-2 text-xs" title="<?= $u['is_active'] ? 'تعطيل' : 'تفعيل' ?>"><i class="fas <?= $u['is_active'] ? 'fa-ban' : 'fa-check' ?>"></i></button>
                                        </form>
                                        <form method="post" onsubmit="return confirm('تأكيد حذف المستخدم نهائياً؟');">
                                            <input type="hidden" name="delete_id" value="<?= $u['id'] ?>">
                                            <button type="submit" class="btn btn-sm btn-danger !py-1 !px-2 text-xs" title="حذف"><i class="fas fa-trash"></i></button>
                                        </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>