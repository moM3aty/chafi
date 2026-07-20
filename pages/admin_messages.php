<?php
// مسار الملف: pages/admin_messages.php

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['Admin', 'SuperAdmin'])) {
    echo "<script>window.location.href='index.php';</script>"; exit;
}

// 1. باتش برمجي سريع للتأكد من وجود عمود admin_reply في الداتابيز (لكي لا ينهار النظام)
try {
    $pdo->query("SELECT admin_reply FROM contact_messages LIMIT 1");
} catch (Exception $e) {
    $pdo->exec("ALTER TABLE contact_messages ADD COLUMN admin_reply TEXT DEFAULT NULL AFTER message");
}

// معالجة القراءة (Mark as read)، الرد، والحذف
if (isset($_POST['action'])) {
    if ($_POST['action'] == 'delete') {
        $pdo->prepare("DELETE FROM contact_messages WHERE id = ?")->execute([(int)$_POST['msg_id']]);
        echo "<script>window.location.href='index.php?page=admin_messages&deleted=1';</script>"; exit;
    } elseif ($_POST['action'] == 'mark_read') {
        $pdo->prepare("UPDATE contact_messages SET status = 'Read' WHERE id = ?")->execute([(int)$_POST['msg_id']]);
        echo "<script>window.location.href='index.php?page=admin_messages';</script>"; exit;
    } elseif ($_POST['action'] == 'reply') {
        $msgId = (int)$_POST['msg_id'];
        $replyText = trim($_POST['admin_reply']);
        if (!empty($replyText)) {
            // تحديث الرسالة بالرد وتغيير حالتها
            $pdo->prepare("UPDATE contact_messages SET admin_reply = ?, status = 'Read' WHERE id = ?")->execute([$replyText, $msgId]);
            echo "<script>window.location.href='index.php?page=admin_messages&replied=1';</script>"; exit;
        }
    }
}

$messages = $pdo->query("SELECT * FROM contact_messages ORDER BY created_at DESC")->fetchAll();
?>

<style>
    /* تنسيق شريط التمرير المخصص للمودال */
    .custom-scroll::-webkit-scrollbar { width: 6px; }
    .custom-scroll::-webkit-scrollbar-track { background: #f1f5f9; border-radius: 8px; }
    .custom-scroll::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 8px; }
    .custom-scroll::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
</style>

<div class="max-w-7xl mx-auto px-4 py-8 mb-14 afiu">
    <div class="flex flex-col sm:flex-row items-center justify-between mb-8 gap-4">
        <h1 class="text-2xl font-black text-pri-900 font-amiri"><i class="fas fa-envelope-open-text text-gld-500 ml-2"></i>رسائل الزوار والاستشارات</h1>
        <a href="index.php?page=admin_dashboard" class="cf-btn cf-btn-out cf-btn-sm bg-white"><i class="fas fa-arrow-right"></i> لوحة القيادة</a>
    </div>

    <?php if(isset($_GET['replied'])): ?>
        <div class="bg-green-50 border-r-4 border-green-500 p-4 rounded-xl text-green-700 font-bold mb-6 shadow-sm"><i class="fas fa-check-circle ml-2"></i> تم إرسال الرد بنجاح. سيظهر للعميل في لوحة التحكم الخاصة به.</div>
    <?php endif; ?>
    <?php if(isset($_GET['deleted'])): ?>
        <div class="bg-red-50 border-r-4 border-red-500 p-4 rounded-xl text-red-700 font-bold mb-6 shadow-sm"><i class="fas fa-trash ml-2"></i> تم حذف الرسالة بنجاح.</div>
    <?php endif; ?>

    <div class="bg-blue-50 p-4 rounded-xl border border-blue-200 mb-6 text-sm text-blue-800 leading-loose shadow-inner">
        <i class="fas fa-info-circle text-blue-600 ml-1"></i> اضغط على زر <b>"عرض ورد"</b> لقراءة نص الاستشارة وكتابة ردك المباشر. الرد سيظهر للعميل في حسابه لتتم الدردشة بسرية.
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
                        <th class="text-center">إجراءات (عرض / رد)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($messages)): ?>
                        <tr><td colspan="6" class="text-center py-10 text-brk-400">لا توجد رسائل مسجلة</td></tr>
                    <?php else: ?>
                        <?php foreach($messages as $m): ?>
                            <tr class="<?= $m['status'] == 'New' ? 'bg-pri-50/50 font-bold' : '' ?> hover:bg-gray-50 transition-colors">
                                <td class="text-pri-900">
                                    <div class="font-bold"><?= htmlspecialchars($m['full_name'] ?? $m['name']) ?></div>
                                    <?php if($m['user_id']): ?><span class="bg-green-100 text-green-700 text-[10px] font-bold px-2 py-0.5 rounded-md mt-1 inline-block"><i class="fas fa-user-check"></i> عضو مسجل</span><?php endif; ?>
                                </td>
                                <td>
                                    <div dir="ltr" class="text-xs text-brk-500 mb-1 text-right"><i class="fas fa-envelope text-gray-400"></i> <?= htmlspecialchars($m['email']) ?></div>
                                    <div dir="ltr" class="text-xs font-bold text-gray-600 text-right"><i class="fas fa-phone-alt text-gray-400"></i> <?= htmlspecialchars($m['phone']) ?></div>
                                </td>
                                <td class="text-pri-700 max-w-[200px]">
                                    <div class="truncate" title="<?= htmlspecialchars($m['subject']) ?>"><?= htmlspecialchars($m['subject']) ?></div>
                                </td>
                                <td class="text-xs text-brk-400 font-mono" dir="ltr"><?= date('Y-m-d H:i', strtotime($m['created_at'])) ?></td>
                                <td>
                                    <?php if(!empty($m['admin_reply'])): ?> 
                                        <span class="badge bg-green-50 text-green-700 border border-green-200 shadow-sm px-3"><i class="fas fa-reply text-[9px] ml-1"></i> تم الرد</span>
                                    <?php elseif($m['status'] == 'New'): ?> 
                                        <span class="badge bg-yellow-100 text-yellow-800 border border-yellow-300 shadow-sm px-3 animate-pulse">جديدة</span> 
                                    <?php else: ?> 
                                        <span class="badge bg-gray-100 text-gray-600 border border-gray-200 px-3">مقروءة</span> 
                                    <?php endif; ?>
                                </td>
                                <td class="flex justify-center gap-2">
                                    <button onclick='openReplyModal(<?= htmlspecialchars(json_encode([
                                        "id" => $m["id"],
                                        "name" => $m["full_name"],
                                        "subject" => $m["subject"],
                                        "message" => $m["message"],
                                        "reply" => $m["admin_reply"] ?? ""
                                    ])) ?>)' class="btn btn-sm btn-primary !py-1.5 !px-3 text-xs shadow-sm" title="قراءة ورد"><i class="fas fa-comments"></i> عرض ورد</button>
                                    
                                    <form method="post" onsubmit="return confirm('تأكيد حذف الرسالة؟');">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="msg_id" value="<?= $m['id'] ?>">
                                        <button type="submit" class="btn btn-sm bg-gray-100 text-gray-500 hover:bg-red-500 hover:text-white !py-1.5 !px-2.5 text-xs transition-colors shadow-sm" title="حذف"><i class="fas fa-trash"></i></button>
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

<!-- مودال قراءة الرسالة والرد (تم تحديثه بالكامل لحل مشكلة القص والتمرير) -->
<div id="replyModal" class="modal-backdrop" style="z-index: 9999999 !important; padding: 1rem;">
    <!-- تعديل الـ max-height لكي لا يخرج عن الشاشة، وإضافة flex column -->
    <div class="modal-dialog w-full relative bg-white rounded-2xl shadow-2xl flex flex-col overflow-hidden mx-auto !p-0" style=" max-width: 650px;max-height: 68vh;margin-top: 100px;">
        
        <!-- زر الإغلاق -->
        <button onclick="closeMdl('replyModal')" class="absolute top-4 left-4 w-8 h-8 bg-gray-100 hover:bg-red-500 hover:text-white rounded-full flex items-center justify-center transition-colors z-50">
            <i class="fas fa-times"></i>
        </button>
        
        <!-- الهيدر الثابت -->
        <div class="modal-header p-5 sm:p-6 border-b border-gray-100 bg-gray-50/50 shrink-0">
            <h2 class="text-xl font-black text-pri-900 font-amiri flex items-center gap-2 justify-center"><i class="fas fa-user-circle text-brk-300"></i> رسالة من: <span id="r_name" class="text-pri-600"></span></h2>
        </div>
        
        <!-- المحتوى القابل للتمرير (Scrollable Body) -->
        <div class="modal-body p-5 sm:p-6 flex-1 overflow-y-auto custom-scroll">
            
            <div class="bg-gray-50 p-4 sm:p-5 rounded-2xl border border-gray-200 mb-6 shadow-inner relative overflow-hidden">
                <div class="absolute right-0 top-0 bottom-0 w-1 bg-pri-300"></div>
                <div class="text-xs text-brk-400 mb-3 font-bold flex items-center gap-1"><i class="fas fa-tag"></i> الموضوع: <span id="r_subject" class="text-pri-900 ml-1"></span></div>
                
                <!-- صندوق الرسالة: تم تحديد ارتفاعه ليسمح بالتمرير الداخلي للرسائل الطويلة جداً -->
                <!-- تم إضافة dir="auto" لكي تظهر الرسائل الإنجليزية من اليسار لليمين بشكل سليم -->
                <div class="text-sm text-brk-700 leading-loose whitespace-pre-wrap font-medium max-h-[35vh] overflow-y-auto pr-2 custom-scroll" id="r_message" dir="auto"></div>
            </div>

            <form method="post" class="mt-auto">
                <input type="hidden" name="action" value="reply">
                <input type="hidden" name="msg_id" id="r_msg_id" value="">
                
                <div class="form-group mb-6">
                    <label class="form-label text-pri-800 font-bold mb-3"><i class="fas fa-reply text-gld-500 ml-1"></i> الـرد على الاستشارة / الرسالة:</label>
                    <textarea name="admin_reply" id="r_admin_reply" class="form-textarea !min-h-[140px] bg-pri-50/30 border-pri-200 focus:border-pri-500 focus:ring focus:ring-pri-200 transition-shadow" placeholder="اكتب ردك هنا... سيظهر هذا الرد للعميل في لوحة التحكم الخاصة به بشكل مباشر." required></textarea>
                </div>
                
                <button type="submit" class="btn btn-gold btn-block btn-lg shadow-lg hover:scale-[1.02] transition-transform"><i class="fas fa-paper-plane"></i> حفظ وإرسال الرد للعميل</button>
            </form>

        </div>
    </div>
</div>

<script>
function openReplyModal(data) {
    document.getElementById('r_msg_id').value = data.id;
    document.getElementById('r_name').innerText = data.name;
    document.getElementById('r_subject').innerText = data.subject || 'بدون موضوع';
    document.getElementById('r_message').innerText = data.message;
    document.getElementById('r_admin_reply').value = data.reply;
    
    // استخدام الدالة الافتراضية لفتح المودال وإعطائه الأولوية
    const modal = document.getElementById('replyModal');
    modal.classList.add('is-active');
    document.body.style.overflow = 'hidden';
}
</script>
```eof