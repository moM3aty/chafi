<?php
// مسار الملف: pages/book_details.php
// الوظيفة: صفحة تفاصيل الكتاب مع نظام الحماية الذكي والمحسن

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$stmt = $pdo->prepare("SELECT b.*, c.name as category_name FROM books b LEFT JOIN categories c ON b.category_id = c.id WHERE b.id = ? AND b.is_active = 1");
$stmt->execute([$id]);
$book = $stmt->fetch();

if (!$book) {
    echo "<div class='text-center py-20'><h1 class='text-3xl font-bold text-pri-900'>الكتاب غير موجود</h1><a href='index.php?page=books' class='btn btn-primary mt-4'>العودة للمكتبة</a></div>";
    return;
}

$isFree = (float)$book['price'] <= 0;
$hasPurchased = false;
$isPendingApproval = false;
$isAdmin = isset($_SESSION['user_role']) && in_array($_SESSION['user_role'], ['Admin', 'SuperAdmin']);

// ════════ نظام حماية المحتوى ومراجعة الطلبات ════════
if (isset($_SESSION['user_id']) && !$isFree && !$isAdmin) {
    try {
        // جلب أحدث طلب للعميل يخص هذا الكتاب لمعرفة حالته
        $stmtCheck = $pdo->prepare("
            SELECT o.status 
            FROM order_items oi 
            JOIN orders o ON oi.order_id = o.id 
            WHERE o.user_id = ? AND oi.item_type = 'book' AND oi.item_id = ?
            ORDER BY o.id DESC LIMIT 1
        ");
        $stmtCheck->execute([$_SESSION['user_id'], $id]);
        $orderData = $stmtCheck->fetch();

        if ($orderData) {
            if (in_array($orderData['status'], ['Processing', 'Shipped', 'Delivered', 'Completed'])) {
                $hasPurchased = true;
            } elseif ($orderData['status'] == 'Pending') {
                $isPendingApproval = true;
            }
        }
    } catch(Exception $e) {}
}
// ════════════════════════════════════════════════════

$canDownload = $isFree || $hasPurchased || $isAdmin;
?>

<div class="max-w-6xl mx-auto px-4 py-8 mb-14">
    <nav class="flex items-center gap-2 text-sm text-brk-400 mb-8 afiu">
        <a href="index.php?page=home" class="hover:text-pri-600 transition"><i class="fas fa-home"></i> الرئيسية</a>
        <i class="fas fa-chevron-left text-[10px]"></i>
        <a href="index.php?page=books" class="hover:text-pri-600 transition">المكتبة</a>
        <i class="fas fa-chevron-left text-[10px]"></i>
        <span class="text-pri-900 font-bold truncate"><?= htmlspecialchars($book['title']) ?></span>
    </nav>

    <div class="bg-white rounded-3xl border-2 border-gray-100 overflow-hidden shadow-sm afiu">
        <div class="p-6 sm:p-10 flex flex-col lg:flex-row gap-10">
            <!-- غلاف الكتاب -->
            <div class="w-full lg:w-1/3 shrink-0">
                <div class="aspect-[3/4] bg-gray-50 rounded-2xl overflow-hidden border border-gray-200 shadow-md relative">
                    <?php if ($isFree): ?><div class="absolute top-4 -right-8 bg-green-500 text-white font-bold text-xs py-1 px-10 transform rotate-45 shadow-sm">مجاني</div><?php endif; ?>
                    <img src="<?= htmlspecialchars($book['thumbnail_url'] ?? 'https://picsum.photos/400/600') ?>" alt="<?= htmlspecialchars($book['title']) ?>" class="w-full h-full object-cover">
                </div>
            </div>

            <!-- تفاصيل الكتاب -->
            <div class="w-full lg:w-2/3 flex flex-col">
                <div class="mb-4">
                    <span class="inline-block bg-pri-50 text-pri-700 font-bold text-xs px-3 py-1 rounded-full mb-3 border border-pri-100"><i class="fas fa-folder-open ml-1"></i> <?= htmlspecialchars($book['category_name'] ?? 'كتاب رقمي') ?></span>
                    <h1 class="text-3xl sm:text-4xl font-black text-pri-900 font-amiri mb-2 leading-tight"><?= htmlspecialchars($book['title']) ?></h1>
                    <?php if ($book['author']): ?>
                        <p class="text-brk-500 text-sm font-bold"><i class="fas fa-pen-nib ml-1"></i> تأليف: <?= htmlspecialchars($book['author']) ?></p>
                    <?php endif; ?>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-8 p-4 bg-gray-50 rounded-2xl border border-gray-100">
                    <div class="text-center"><div class="text-[10px] text-brk-400 mb-1">عدد التحميلات</div><div class="font-bold text-pri-900"><?= $book['download_count'] ?></div></div>
                    <div class="text-center"><div class="text-[10px] text-brk-400 mb-1">صيغة الملف</div><div class="font-bold text-pri-900 font-mono" dir="ltr">PDF</div></div>
                    <div class="text-center"><div class="text-[10px] text-brk-400 mb-1">إمكانية القراءة</div><div class="font-bold text-pri-900">متاحة (بعد الشراء)</div></div>
                    <div class="text-center"><div class="text-[10px] text-brk-400 mb-1">الطباعة</div><div class="font-bold text-pri-900">متاحة</div></div>
                </div>

                <div class="mb-8 flex-1">
                    <h3 class="text-lg font-bold text-pri-900 mb-3 border-b border-gray-100 pb-2">نبذة عن الكتاب</h3>
                    <div class="prose prose-sm max-w-none text-brk-600 leading-loose custom-html-content bg-white p-5 rounded-2xl border border-gray-50">
                        <?= $book['description'] ?: 'لا يوجد وصف متاح حالياً.' ?>
                    </div>
                </div>

                <!-- خيارات الدفع أو التحميل أو المراجعة -->
                <div class="pt-6 border-t border-gray-100 bg-gray-50/50 -mx-6 sm:-mx-10 -mb-6 sm:-mb-10 p-6 sm:p-10 rounded-b-3xl">
                    <div class="flex items-center justify-between mb-4">
                        <span class="text-sm font-bold text-brk-500">سعر الكتاب:</span>
                        <span class="text-3xl font-black text-pri-700"><?= $isFree ? '<span class="text-green-600">مجاني</span>' : number_format($book['price'], 2) . ' ر.س' ?></span>
                    </div>

                    <div class="flex flex-wrap gap-4">
                        <?php if ($canDownload): ?>
                            <!-- الزر الأخضر للتحميل يوجه إلى الموجه النقي -->
                            <a href="ajax/download_book.php?id=<?= $book['id'] ?>" target="_blank" class="btn btn-primary btn-lg flex-1 shadow-lg hover:scale-105 transition-transform !py-4 text-lg">
                                <i class="fas fa-cloud-download-alt"></i> تحميل وقراءة الكتاب (PDF)
                            </a>
                        
                        <?php elseif ($isPendingApproval): ?>
                            <!-- التنبيه الأصفر (يظهر إذا طلب العميل الكتاب ورفع الإيصال ولكن الإدارة لم توافق بعد) -->
                            <div class="flex-1 bg-yellow-50 border border-yellow-200 text-yellow-700 rounded-xl flex flex-col items-center justify-center font-bold text-sm h-16 shadow-sm">
                                <span class="flex items-center gap-2"><i class="fas fa-hourglass-half animate-spin"></i> طلبك قيد المراجعة</span>
                                <span class="text-[10px] mt-1 font-normal text-yellow-600">بانتظار تأكيد الإدارة للتحويل البنكي لفتح التحميل</span>
                            </div>
                        
                        <?php else: ?>
                            <!-- زر الإضافة للسلة (يظهر للعميل الجديد الذي لم يطلب الكتاب بعد) -->
                            <button onclick="addToCart('book', <?= $book['id'] ?>)" class="btn btn-gold btn-lg flex-1 shadow-lg hover:scale-105 transition-transform !py-4 text-lg">
                                <i class="fas fa-cart-arrow-down"></i> إضافة للسلة لطلب الكتاب
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>