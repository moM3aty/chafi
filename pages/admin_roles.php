<?php
// مسار الملف: pages/admin_roles.php

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'SuperAdmin') {
    echo "<script>alert('عذراً، هذه الصفحة مخصصة للمدير العام فقط'); window.location.href='index.php?page=admin_dashboard';</script>"; exit;
}

if (isset($_POST['assign_role'])) {
    $userId = (int)$_POST['user_id'];
    $roleName = $_POST['role_name'];
    $pdo->prepare("UPDATE users SET role = ? WHERE id = ?")->execute([$roleName, $userId]);
    echo "<script>window.location.href='index.php?page=admin_roles&updated=1';</script>"; exit;
}

$users = $pdo->query("SELECT * FROM users ORDER BY created_at DESC")->fetchAll();
?>

<div class="max-w-7xl mx-auto px-4 py-8 mb-14 afiu">
    <div class="flex items-center justify-between mb-8 gap-4">
        <h1 class="text-2xl font-black text-pri-900 font-amiri"><i class="fas fa-user-shield text-gld-500 ml-2"></i>إدارة الصلاحيات والمستخدمين</h1>
        <a href="index.php?page=admin_settings" class="cf-btn cf-btn-out cf-btn-sm bg-white text-xs"><i class="fas fa-arrow-right"></i> العودة للإعدادات</a>
    </div>

    <?php if(isset($_GET['updated'])): ?>
        <div class="bg-green-50 border-r-4 border-green-500 p-4 rounded-xl text-green-700 font-bold mb-6">تم تحديث صلاحية المستخدم بنجاح.</div>
    <?php endif; ?>

    <div class="erp-card overflow-hidden shadow-sm">
        <div class="table-responsive">
            <table class="erp-table">
                <thead>
                    <tr>
                        <th>المستخدم</th>
                        <th>البريد الإلكتروني</th>
                        <th>الصلاحيات الحالية</th>
                        <th class="text-center">تحديث الصلاحية</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($users)): ?>
                        <tr><td colspan="4" class="text-center py-10 text-brk-400">لا يوجد مستخدمين مسجلين</td></tr>
                    <?php else: ?>
                        <?php foreach ($users as $user): ?>
                            <tr class="border-b border-gray-50 hover:bg-gray-50 transition">
                                <td class="font-bold text-pri-900"><?= htmlspecialchars($user['full_name']) ?></td>
                                <td class="text-brk-500 text-sm" dir="ltr"><?= htmlspecialchars($user['email']) ?></td>
                                <td>
                                    <?php if ($user['role'] == 'SuperAdmin'): ?>
                                        <span class="bg-gld-100 text-gld-800 px-3 py-1 rounded-lg text-xs font-bold shadow-sm border border-gld-200">مدير عام</span>
                                    <?php elseif ($user['role'] == 'Admin'): ?>
                                        <span class="bg-pri-50 text-pri-700 px-3 py-1 rounded-lg text-xs font-bold border border-pri-100">مدير النظام</span>
                                    <?php else: ?>
                                        <span class="bg-gray-100 text-gray-600 px-3 py-1 rounded-lg text-xs font-bold border border-gray-200">مستخدم عادي</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <form method="post" class="flex justify-center gap-2 items-center">
                                        <input type="hidden" name="assign_role" value="1">
                                        <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                        <select name="role_name" class="cf-select !py-1 !px-2 text-xs w-auto min-w-[130px]">
                                            <option value="User" <?= $user['role']=='User'?'selected':'' ?>>مستخدم عادي</option>
                                            <option value="Admin" <?= $user['role']=='Admin'?'selected':'' ?>>مدير (Admin)</option>
                                            <option value="SuperAdmin" <?= $user['role']=='SuperAdmin'?'selected':'' ?>>مدير عام (SuperAdmin)</option>
                                        </select>
                                        <button type="submit" class="cf-btn cf-btn-pri !py-1.5 !px-3 text-xs" title="تحديث الصلاحية"><i class="fas fa-check"></i></button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>