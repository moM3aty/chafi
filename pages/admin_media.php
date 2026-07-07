<?php
// مسار الملف: pages/admin_media.php

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['Admin', 'SuperAdmin'])) {
    echo "<script>window.location.href='index.php';</script>"; exit;
}

// رفع ملف جديد
 $msg = ''; $msgType = '';
if (isset($_POST['upload_media']) && isset($_FILES['media_file'])) {
    $file = $_FILES['media_file'];
    if ($file['error'] == 0) {
        $allowedTypes = ['image/jpeg','image/png','image/gif','image/webp','video/mp4','audio/mpeg','audio/mp3','application/pdf'];
        $fType = in_array($file['type'], $allowedTypes) ? (strpos($file['type'], 'image') !== false ? 'image' : (strpos($file['type'], 'video') !== false ? 'video' : (strpos($file['type'], 'audio') !== false ? 'audio' : 'document'))) : 'other';

        $uploadDir = 'assets/uploads/media/';
        if (!is_dir($uploadDir)) { mkdir($uploadDir, 0777, true); }
        $fileName = time() . '_' . basename($file['name']);
        if (move_uploaded_file($file['tmp_name'], $uploadDir . $fileName)) {
            $filePath = $uploadDir . $fileName;
            $fileUrl = $filePath;
            $dimensions = '';
            if ($fType == 'image') {
                $size = getimagesize($filePath);
                if ($size) $dimensions = $size[0] . 'x' . $size[1];
            }
            $stmt = $pdo->prepare("INSERT INTO media (file_name, file_path, file_url, file_type, mime_type, file_size, dimensions, uploaded_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$file['name'], $filePath, $fileUrl, $fType, $file['type'], $file['size'], $dimensions, $_SESSION['user_id']]);
            $msg = "تم رفع الملف بنجاح!"; $msgType = "ok";
        } else {
            $msg = "فشل رفع الملف."; $msgType = "err";
        }
    }
}

// حذف ملف
if (isset($_POST['delete_id'])) {
    $delId = (int)$_POST['delete_id'];
    $f = $pdo->prepare("SELECT file_path FROM media WHERE id = ?");
    $f->execute([$delId]);
    $fp = $f->fetchColumn();
    if ($fp && file_exists($fp)) { @unlink($fp); }
    $pdo->prepare("DELETE FROM media WHERE id = ?")->execute([$delId]);
    echo "<script>window.location.href='index.php?page=admin_media';</script>"; exit;
}

 $mediaFiles = $pdo->query("SELECT m.*, u.full_name as uploader FROM media m LEFT JOIN users u ON m.uploaded_by = u.id ORDER BY m.created_at DESC LIMIT 50")->fetchAll();

function formatSize($bytes) {
    if ($bytes >= 1048576) return round($bytes / 1048576, 1) . ' MB';
    if ($bytes >= 1024) return round($bytes / 1024, 1) . ' KB';
    return $bytes . ' B';
}
?>

<div class="max-w-7xl mx-auto px-4 py-8 mb-14 afiu">
    <div class="flex items-center justify-between mb-8 gap-4">
        <h1 class="text-2xl font-black text-pri-900 font-amiri"><i class="fas fa-photo-video text-gld-500 ml-2"></i>المكتبة الرقمية</h1>
        <a href="index.php?page=admin_dashboard" class="cf-btn cf-btn-out cf-btn-sm bg-white"><i class="fas fa-arrow-right"></i> لوحة القيادة</a>
    </div>

    <?php if($msg): ?>
        <div class="p-4 rounded-xl mb-6 font-bold <?= $msgType == 'ok' ? 'bg-green-50 text-green-700 border-r-4 border-green-500' : 'bg-red-50 text-red-700 border-r-4 border-red-500' ?>"><?= $msg ?></div>
    <?php endif; ?>

    <!-- رفع ملف -->
    <div class="erp-card p-6 mb-8">
        <h3 class="font-bold text-pri-900 mb-4"><i class="fas fa-cloud-upload-alt text-gld-500 ml-1"></i> رفع ملف جديد</h3>
        <form method="post" enctype="multipart/form-data" class="flex flex-col sm:flex-row gap-4 items-end">
            <div class="form-group !mb-0 flex-1">
                <input type="file" name="media_file" class="form-control !py-2" accept="image/*,video/*,audio/*,.pdf" required>
            </div>
            <button type="submit" name="upload_media" value="1" class="btn btn-primary h-[46px] px-6 shrink-0"><i class="fas fa-upload"></i> رفع</button>
        </form>
        <p class="text-[10px] text-brk-400 mt-2">الصيغ المقبولة: JPEG, PNG, GIF, WebP, MP4, MP3, PDF</p>
    </div>

    <!-- عرض الملفات -->
    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
        <?php if(empty($mediaFiles)): ?>
            <div class="col-span-full text-center py-14 text-brk-400"><i class="fas fa-folder-open text-4xl mb-3 block opacity-25"></i><p>المكتبة فارغة</p></div>
        <?php else: ?>
            <?php foreach($mediaFiles as $m): ?>
                <div class="erp-card overflow-hidden group relative">
                    <?php if($m['file_type'] == 'image'): ?>
                        <div class="aspect-square bg-gray-50 overflow-hidden">
                            <img src="<?= htmlspecialchars($m['file_url']) ?>" class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-110">
                        </div>
                    <?php else: ?>
                        <div class="aspect-square bg-gray-50 flex items-center justify-center">
                            <i class="fas <?= $m['file_type'] == 'video' ? 'fa-video text-purple-500' : ($m['file_type'] == 'audio' ? 'fa-music text-blue-500' : 'fa-file-pdf text-red-500') ?> text-4xl opacity-50"></i>
                        </div>
                    <?php endif; ?>
                    <div class="p-2.5">
                        <div class="text-[10px] text-brk-400 truncate" title="<?= htmlspecialchars($m['file_name']) ?>"><?= htmlspecialchars($m['file_name']) ?></div>
                        <div class="text-[10px] text-brk-300"><?= formatSize($m['file_size']) ?></div>
                    </div>
                    <div class="absolute top-2 left-2 flex gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                        <button onclick="navigator.clipboard.writeText('<?= htmlspecialchars($m['file_url']) ?>'); showToast('تم نسخ الرابط','ok')" class="w-7 h-7 rounded-full bg-white/90 text-pri-600 text-xs flex items-center justify-center shadow" title="نسخ الرابط"><i class="fas fa-link"></i></button>
                        <form method="post" onsubmit="return confirm('حذف الملف؟');">
                            <input type="hidden" name="delete_id" value="<?= $m['id'] ?>">
                            <button type="submit" class="w-7 h-7 rounded-full bg-red-500 text-white text-xs flex items-center justify-center shadow" title="حذف"><i class="fas fa-trash"></i></button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>