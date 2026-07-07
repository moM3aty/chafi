<?php
// مسار الملف: pages/admin_messages.php

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['Admin', 'SuperAdmin'])) {
    echo "<script>window.location.href='index.php';</script>"; exit;
}

// معالجة القراءة (Mark as read) والحذف
if (isset($_POST['action'])) {
    if ($_POST['action'] == 'delete') {
        $pdo->prepare("DELETE FROM contact_messages WHERE id = ?")->execute([(int)$_POST['msg_id']]);
        echo "<script>window.location.href='index.php?page=admin_messages';</script>"; exit;
    } elseif ($_POST['action'] == 'mark_read') {
        $pdo->prepare("UPDATE contact_messages SET status = 'Read' WHERE id = ?")->execute([(int)$_POST['msg_id']]);
        echo "<script>window.location.href='index.php?page=admin_messages';</script>"; exit;
    }
}

$messages = $pdo->query("SELECT * FROM contact_messages ORDER BY created_at DESC")->fetchAll();
?>

<div class="max-w-7xl mx-auto px-4 py-8 mb-14 afiu">
    <div class="flex items-center justify-between mb-8">
        <h1 class="text-2xl font-black text-pri-900 font-amiri"><i class="fas fa-envelope-open-text text-gld-500 ml-2"></i>رسائل الزوار</h1>
        <a href="index.php?page=admin_dashboard" class="cf-btn cf-btn-out cf-btn-sm bg-white"><i class="fas fa-arrow-right"></i> لوحة القيادة</a>
    </div>

    <div class="erp-card overflow-hidden">
        <div class="table-responsive">
            <table class="erp-table">
                <thead>
                    <tr>
                        <th>المرسل</th>
                        <th>البريد والهاتف</th>
                        <th>الموضوع</th>
                        <th>التاريخ</th>
                        <th>الحالة</th>
                        <th class="text-center">إجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($messages)): ?>
                        <tr><td colspan="6" class="text-center py-10 text-brk-400">لا توجد رسائل مسجلة</td></tr>
                    <?php else: ?>
                        <?php foreach($messages as $m): ?>
                            <tr class="<?= $m['status'] == 'New' ? 'bg-pri-50/50 font-bold' : '' ?>">
                                <td class="text-pri-900"><?= htmlspecialchars($m['full_name'] ?? $m['name']) ?></td>
                                <td>
                                    <div dir="ltr" class="text-xs text-brk-500"><?= htmlspecialchars($m['email']) ?></div>
                                    <div dir="ltr" class="text-xs font-bold text-gray-500"><?= htmlspecialchars($m['phone']) ?></div>
                                </td>
                                <td class="text-pri-700 truncate max-w-[150px]" title="<?= htmlspecialchars($m['subject']) ?>"><?= htmlspecialchars($m['subject']) ?></td>
                                <td class="text-xs text-brk-400" dir="ltr"><?= date('Y-m-d H:i', strtotime($m['created_at'])) ?></td>
                                <td>
                                    <?php if($m['status'] == 'New'): ?> <span class="badge badge-warning">جديدة</span> <?php else: ?> <span class="badge bg-gray-100 text-gray-500">مقروءة</span> <?php endif; ?>
                                </td>
                                <td class="flex justify-center gap-2">
                                    <?php if($m['status'] == 'New'): ?>
                                        <form method="post">
                                            <input type="hidden" name="action" value="mark_read">
                                            <input type="hidden" name="msg_id" value="<?= $m['id'] ?>">
                                            <button type="submit" class="btn btn-sm btn-outline !py-1 !px-2 text-xs" title="تحديد كمقروء"><i class="fas fa-check"></i></button>
                                        </form>
                                    <?php endif; ?>
                                    <button onclick="alert('الرسالة:\n<?= addslashes(htmlspecialchars($m['message'])) ?>')" class="btn btn-sm btn-primary !py-1 !px-2 text-xs" title="قراءة النص"><i class="fas fa-eye"></i></button>
                                    <form method="post" onsubmit="return confirm('تأكيد حذف الرسالة؟');">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="msg_id" value="<?= $m['id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-danger !py-1 !px-2 text-xs" title="حذف"><i class="fas fa-trash"></i></button>
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