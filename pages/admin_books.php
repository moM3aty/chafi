<?php
// مسار الملف: pages/admin_books.php
// الوظيفة: قائمة الكتب الخاصة بالإدارة
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['Admin', 'SuperAdmin'])) {
    echo "<script>window.location.href='index.php';</script>"; exit;
}

try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS books (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        slug VARCHAR(255) UNIQUE NOT NULL,
        author VARCHAR(255),
        category_id INT,
        price DECIMAL(10,2) DEFAULT 0.00,
        description TEXT,
        thumbnail_url VARCHAR(255),
        book_file_url VARCHAR(255),
        download_count INT DEFAULT 0,
        is_active TINYINT(1) DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
} catch (Exception $e) {}

if (isset($_POST['delete_id'])) {
    $delId = (int)$_POST['delete_id'];
    $pdo->prepare("DELETE FROM books WHERE id = ?")->execute([$delId]);
    echo "<script>window.location.href='index.php?page=admin_books&deleted=1';</script>"; exit;
}

$books = $pdo->query("SELECT b.*, c.name as cat_name FROM books b LEFT JOIN categories c ON b.category_id = c.id ORDER BY b.id DESC")->fetchAll();
?>

<div class="max-w-7xl mx-auto px-4 py-8 mb-14 afiu">
    <div class="flex flex-col sm:flex-row items-center justify-between mb-8 gap-4">
        <h1 class="text-2xl font-black text-pri-900 font-amiri"><i class="fas fa-book-open text-gld-500 ml-2"></i>إدارة الكتب والمكتبة</h1>
        <div class="flex gap-2">
            <a href="index.php?page=admin_dashboard" class="cf-btn cf-btn-out cf-btn-sm bg-white"><i class="fas fa-arrow-right"></i> لوحة القيادة</a>
            <a href="index.php?page=admin_book_form" class="cf-btn cf-btn-pri cf-btn-sm"><i class="fas fa-plus"></i> كتاب جديد</a>
        </div>
    </div>

    <?php if(isset($_GET['deleted'])): ?>
        <div class="bg-green-50 border-r-4 border-green-500 p-4 rounded-xl text-green-700 font-bold mb-6">تم حذف الكتاب بنجاح.</div>
    <?php endif; ?>

    <div class="erp-card overflow-hidden">
        <div class="table-responsive">
            <table class="erp-table">
                <thead>
                    <tr>
                        <th>صورة</th>
                        <th>الكتاب والمؤلف</th>
                        <th>القسم</th>
                        <th>السعر</th>
                        <th>التحميلات</th>
                        <th>الحالة</th>
                        <th class="text-center">الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($books)): ?>
                        <tr><td colspan="7" class="text-center py-10 text-brk-400">لا توجد كتب مسجلة</td></tr>
                    <?php else: ?>
                        <?php foreach($books as $b): ?>
                            <tr>
                                <td>
                                    <img src="<?= htmlspecialchars($b['thumbnail_url'] ?? 'https://picsum.photos/100/150') ?>" class="w-12 h-16 rounded object-cover border border-gray-200">
                                </td>
                                <td>
                                    <div class="font-bold text-pri-900 line-clamp-1 max-w-[200px]"><?= htmlspecialchars($b['title']) ?></div>
                                    <div class="text-[10px] text-gray-400 mt-1"><i class="fas fa-pen-nib"></i> <?= htmlspecialchars($b['author'] ?? 'غير محدد') ?></div>
                                </td>
                                <td class="text-sm text-brk-500"><?= htmlspecialchars($b['cat_name'] ?? 'بدون قسم') ?></td>
                                <td>
                                    <div class="font-black text-pri-700"><?= $b['price'] > 0 ? number_format($b['price'], 2) . ' ر.س' : '<span class="text-green-600">مجاني</span>' ?></div>
                                </td>
                                <td><span class="badge bg-blue-50 text-blue-600"><i class="fas fa-download ml-1"></i> <?= $b['download_count'] ?></span></td>
                                <td>
                                    <?php if($b['is_active']): ?> <span class="badge badge-success">مفعل</span> <?php else: ?> <span class="badge badge-danger">معطل</span> <?php endif; ?>
                                </td>
                                <td class="flex justify-center gap-2">
                                    <a href="index.php?page=admin_book_form&id=<?= $b['id'] ?>" class="btn btn-sm btn-outline !py-1 !px-2 text-xs" title="تعديل"><i class="fas fa-edit"></i></a>
                                    <form method="post" onsubmit="return confirm('تأكيد الحذف نهائياً؟');">
                                        <input type="hidden" name="delete_id" value="<?= $b['id'] ?>">
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