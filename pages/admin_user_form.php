<?php
// مسار الملف: pages/admin_user_form.php

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['Admin', 'SuperAdmin'])) {
    echo "<script>window.location.href='index.php';</script>"; exit;
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$isEdit = $id > 0;
$msg = ''; $msgType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName = $_POST['full_name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'] ?? '';
    $role = $_POST['role'];
    $isActive = isset($_POST['is_active']) ? 1 : 0;
    $password = $_POST['password'] ?? '';

    try {
        if ($isEdit) {
            if (!empty($password)) {
                $hashed = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE users SET full_name=?, email=?, phone=?, role=?, is_active=?, password=? WHERE id=?");
                $stmt->execute([$fullName, $email, $phone, $role, $isActive, $hashed, $id]);
            } else {
                $stmt = $pdo->prepare("UPDATE users SET full_name=?, email=?, phone=?, role=?, is_active=? WHERE id=?");
                $stmt->execute([$fullName, $email, $phone, $role, $isActive, $id]);
            }
            $msg = "تم تحديث المستخدم بنجاح!"; $msgType = "ok";
        } else {
            if (empty($password)) { $msg = "كلمة المرور مطلوبة للمستخدم الجديد."; $msgType = "err"; }
            else {
                $hashed = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO users (full_name, email, password, phone, role, is_active) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([$fullName, $email, $hashed, $phone, $role, $isActive]);
                $id = $pdo->lastInsertId(); $isEdit = true;
                $msg = "تم إنشاء المستخدم بنجاح!"; $msgType = "ok";
            }
        }
    } catch (PDOException $e) {
        $msg = "حدث خطأ: تأكد من عدم تكرار البريد الإلكتروني."; $msgType = "err";
    }
}

$user = null;
if ($isEdit) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$id]);
    $user = $stmt->fetch();
}
?>

<div class="max-w-4xl mx-auto px-4 py-8 mb-14 afiu">
    <div class="flex items-center justify-between mb-8">
        <h1 class="text-2xl font-black text-pri-900 font-amiri"><i class="fas <?= $isEdit ? 'fa-user-edit' : 'fa-user-plus' ?> text-gld-500 ml-2"></i><?= $isEdit ? 'تعديل مستخدم' : 'إضافة مستخدم جديد' ?></h1>
        <a href="index.php?page=admin_users" class="cf-btn cf-btn-out cf-btn-sm bg-white"><i class="fas fa-arrow-right"></i> العودة</a>
    </div>

    <?php if($msg): ?>
        <div class="p-4 rounded-xl mb-6 font-bold <?= $msgType == 'ok' ? 'bg-green-50 text-green-700 border-r-4 border-green-500' : 'bg-red-50 text-red-700 border-r-4 border-red-500' ?>"><?= $msg ?></div>
    <?php endif; ?>

    <div class="erp-card p-6 sm:p-10">
        <form method="post">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div class="form-group !mb-0">
                    <label class="form-label">الاسم الكامل <span class="req">*</span></label>
                    <input type="text" name="full_name" class="form-control" value="<?= htmlspecialchars($user['full_name'] ?? '') ?>" required>
                </div>
                <div class="form-group !mb-0">
                    <label class="form-label">البريد الإلكتروني <span class="req">*</span></label>
                    <input type="email" name="email" dir="ltr" class="form-control" value="<?= htmlspecialchars($user['email'] ?? '') ?>" required>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div class="form-group !mb-0">
                    <label class="form-label">رقم الجوال</label>
                    <input type="tel" name="phone" dir="ltr" class="form-control" value="<?= htmlspecialchars($user['phone'] ?? '') ?>">
                </div>
                <div class="form-group !mb-0">
                    <label class="form-label">الصلاحية <span class="req">*</span></label>
                    <select name="role" class="form-select" required>
                        <option value="User" <?= ($user['role'] ?? '') == 'User' ? 'selected' : '' ?>>مستخدم عادي</option>
                        <option value="Admin" <?= ($user['role'] ?? '') == 'Admin' ? 'selected' : '' ?>>مدير (Admin)</option>
                        <option value="SuperAdmin" <?= ($user['role'] ?? '') == 'SuperAdmin' ? 'selected' : '' ?>>مدير عام (SuperAdmin)</option>
                    </select>
                </div>
            </div>

            <div class="form-group mb-6">
                <label class="form-label"><?= $isEdit ? 'كلمة المرور الجديدة (اتركها فارغة لعدم التغيير)' : 'كلمة المرور <span class="req">*</span>' ?></label>
                <input type="password" name="password" class="form-control" <?= !$isEdit ? 'required' : '' ?> placeholder="<?= $isEdit ? 'اتركها فارغة إذا لا تريد التغيير' : 'أدخل كلمة مرور قوية' ?>">
            </div>

            <div class="flex flex-wrap gap-6 mb-8 border-t border-gray-100 pt-6 bg-gray-50 -mx-6 px-6 pb-6 rounded-b-3xl">
                <label class="toggle-switch mt-4">
                    <input type="checkbox" name="is_active" value="1" <?= (!isset($user) || $user['is_active'] == 1) ? 'checked' : '' ?>>
                    <span class="toggle-slider"></span>
                    <span class="mr-3 font-bold text-pri-900">حساب مفعّل</span>
                </label>
            </div>

            <div class="-mt-4">
                <button type="submit" class="btn btn-primary btn-lg shadow-xl w-full sm:w-auto"><i class="fas fa-save"></i> حفظ المستخدم</button>
            </div>
        </form>
    </div>
</div>