<?php
// مسار الملف: pages/admin_settings_form.php

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['Admin', 'SuperAdmin'])) {
    echo "<script>window.location.href='index.php';</script>"; exit;
}

$msg = ''; $msgType = '';

// إضافة إعدادات الصوت في الخلفية تلقائياً إذا لم تكن موجودة في قاعدة البيانات
try {
    $pdo->exec("INSERT IGNORE INTO settings (setting_key, setting_value, setting_type, setting_group, label_ar) VALUES 
        ('bg_audio', 'https://server11.mp3quran.net/hazza/015.mp3', 'text', 'general', 'رابط المقطع الصوتي للخلفية (مثل سورة الحجر)'),
        ('enable_bg_audio', '1', 'boolean', 'general', 'تفعيل تشغيل القرآن في الخلفية للزوار')
    ");
} catch(Exception $e) {}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($_POST as $key => $value) {
        if (strpos($key, 'setting_') === 0) {
            $settingKey = str_replace('setting_', '', $key);
            $stmt = $pdo->prepare("SELECT id FROM settings WHERE setting_key = ?");
            $stmt->execute([$settingKey]);
            if ($stmt->fetch()) {
                $pdo->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = ?")->execute([$value, $settingKey]);
            } else {
                $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)")->execute([$settingKey, $value]);
            }
        }
    }
    $msg = "تم حفظ جميع الإعدادات بنجاح!"; $msgType = "ok";
}

$settings = $pdo->query("SELECT * FROM settings ORDER BY setting_group, id")->fetchAll();
$groups = [];
foreach ($settings as $s) {
    $groups[$s['setting_group']][] = $s;
}
?>

<div class="max-w-5xl mx-auto px-4 py-8 mb-14 afiu">
    <div class="flex items-center justify-between mb-8">
        <h1 class="text-2xl font-black text-pri-900 font-amiri"><i class="fas fa-sliders-h text-gld-500 ml-2"></i>تعديل إعدادات الموقع</h1>
        <a href="index.php?page=admin_settings" class="cf-btn cf-btn-out cf-btn-sm bg-white"><i class="fas fa-arrow-right"></i> عرض الإعدادات</a>
    </div>

    <?php if($msg): ?>
        <div class="p-4 rounded-xl mb-6 font-bold <?= $msgType == 'ok' ? 'bg-green-50 text-green-700 border-r-4 border-green-500' : 'bg-red-50 text-red-700 border-r-4 border-red-500' ?>"><?= $msg ?></div>
    <?php endif; ?>

    <form method="post">
        <?php
        $groupNames = ['general' => 'الإعدادات العامة والتشغيل', 'seo' => 'إعدادات SEO', 'social' => 'التواصل الاجتماعي', 'payment' => 'المدفوعات'];
        foreach ($groups as $groupKey => $items):
        ?>
            <div class="erp-card overflow-hidden mb-8">
                <div class="bg-gray-50 px-6 py-4 border-b border-gray-100">
                    <h3 class="font-black text-pri-900"><i class="fas fa-cog text-gld-500 ml-2"></i><?= $groupNames[$groupKey] ?? $groupKey ?></h3>
                </div>
                <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                    <?php foreach($items as $s): ?>
                        <div class="form-group !mb-0">
                            <label class="form-label"><?= htmlspecialchars($s['label_ar'] ?: $s['setting_key']) ?></label>
                            <?php if($s['setting_type'] == 'boolean'): ?>
                                <select name="setting_<?= htmlspecialchars($s['setting_key']) ?>" class="form-select">
                                    <option value="1" <?= $s['setting_value'] == '1' ? 'selected' : '' ?>>نعم (مفعّل)</option>
                                    <option value="0" <?= $s['setting_value'] == '0' ? 'selected' : '' ?>>لا (معطّل)</option>
                                </select>
                            <?php elseif($s['setting_type'] == 'color'): ?>
                                <div class="flex gap-2">
                                    <input type="color" name="setting_<?= htmlspecialchars($s['setting_key']) ?>" value="<?= htmlspecialchars($s['setting_value']) ?>" class="w-12 h-10 rounded border-0 p-0 cursor-pointer">
                                    <input type="text" class="form-control flex-1" disabled value="<?= htmlspecialchars($s['setting_value']) ?>">
                                </div>
                            <?php elseif($s['setting_type'] == 'number'): ?>
                                <input type="number" name="setting_<?= htmlspecialchars($s['setting_key']) ?>" class="form-control" value="<?= htmlspecialchars($s['setting_value']) ?>" dir="ltr">
                            <?php elseif($s['setting_type'] == 'image'): ?>
                                <input type="url" name="setting_<?= htmlspecialchars($s['setting_key']) ?>" class="form-control" dir="ltr" value="<?= htmlspecialchars($s['setting_value']) ?>" placeholder="رابط الصورة">
                                <?php if(!empty($s['setting_value'])): ?><img src="<?= htmlspecialchars($s['setting_value']) ?>" class="w-12 h-12 rounded-lg object-cover border border-gray-200 mt-2"><?php endif; ?>
                            <?php else: ?>
                                <input type="text" name="setting_<?= htmlspecialchars($s['setting_key']) ?>" class="form-control" value="<?= htmlspecialchars($s['setting_value']) ?>" dir="ltr">
                            <?php endif; ?>
                            <input type="hidden" name="key_<?= htmlspecialchars($s['setting_key']) ?>" value="1">
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>

        <button type="submit" class="btn btn-gold btn-lg shadow-xl w-full sm:w-auto"><i class="fas fa-save"></i> حفظ جميع الإعدادات</button>
    </form>
</div>