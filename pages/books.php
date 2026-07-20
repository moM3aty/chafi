<?php
// مسار الملف: pages/books.php
// الوظيفة: عرض الكتب والملفات الرقمية للعملاء

$stmt = $pdo->prepare("
    SELECT b.*, c.name as category_name 
    FROM books b 
    LEFT JOIN categories c ON b.category_id = c.id 
    WHERE b.is_active = 1 
    ORDER BY b.id DESC
");
$stmt->execute();
$books = $stmt->fetchAll();

// جلب حالة مشتريات العميل الحالي من الكتب ليكون الزر ذكياً في المعرض
$userId = $_SESSION['user_id'] ?? 0;
$purchasedBooks = [];
$pendingBooks = [];
$isAdmin = isset($_SESSION['user_role']) && in_array($_SESSION['user_role'], ['Admin', 'SuperAdmin']);

if ($userId && !$isAdmin) {
    // جلب أحدث طلب لكل كتاب
    $stmtOrders = $pdo->prepare("
        SELECT oi.item_id, o.status 
        FROM order_items oi 
        JOIN orders o ON oi.order_id = o.id 
        WHERE o.user_id = ? AND oi.item_type = 'book'
        ORDER BY o.id ASC
    ");
    $stmtOrders->execute([$userId]);
    while($row = $stmtOrders->fetch()) {
        if (in_array($row['status'], ['Processing', 'Shipped', 'Delivered', 'Completed'])) {
            $purchasedBooks[] = $row['item_id'];
        } elseif ($row['status'] == 'Pending') {
            $pendingBooks[] = $row['item_id'];
        }
    }
}
?>

<div class="max-w-7xl mx-auto px-4 py-12 mb-14">
    
    <div class="text-center mb-12 afiu">
        <div class="w-20 h-20 rounded-full bg-gld-50 text-gld-600 flex items-center justify-center text-4xl mx-auto mb-4 shadow-sm border border-gld-100">
            <i class="fas fa-book-open"></i>
        </div>
        <h1 class="text-3xl sm:text-4xl font-black text-pri-900 font-amiri mb-3">المكتبة الرقمية</h1>
        <div class="w-16 h-1 bg-gld-500 mx-auto rounded-full mt-3 mb-4"></div>
        <p class="text-brk-500 max-w-2xl mx-auto text-sm leading-relaxed">
            مكتبة متكاملة تضم كتباً وملفات رقمية قيمة وموثوقة، يمكنك تصفحها وتحميلها مباشرة لتكون مرجعاً لك في الرقية والتحصين.
        </p>
    </div>

    <?php if (empty($books)): ?>
        <div class="bg-white rounded-3xl border border-dashed border-gray-300 p-16 text-center shadow-sm afiu" style="animation-delay: 0.1s">
            <i class="fas fa-book-open text-6xl text-brk-200 mb-4 opacity-50"></i>
            <h3 class="text-xl font-bold text-pri-900 mb-2">المكتبة قيد التجهيز</h3>
            <p class="text-brk-400">يتم حالياً تجهيز وإضافة الكتب القيمة، تفضل بزيارة الصفحة لاحقاً.</p>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 afiu" style="animation-delay: 0.1s">
            <?php foreach ($books as $book): 
                $isFree = (float)$book['price'] <= 0;
                $hasPurchased = in_array($book['id'], $purchasedBooks);
                $isPending = in_array($book['id'], $pendingBooks);
                // إذا كان الكتاب تم شراؤه، يجب أن نلغي حالة المراجعة عنه
                if ($hasPurchased) $isPending = false;
                
                $canDownload = $isFree || $hasPurchased || $isAdmin;
            ?>
            <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden hover:shadow-xl hover:border-pri-200 transition-all duration-300 group flex flex-col relative">
                
                <?php if ($isFree): ?>
                    <div class="absolute top-4 -right-8 bg-green-500 text-white font-bold text-[10px] py-1 px-8 transform rotate-45 shadow-md z-10 uppercase tracking-widest">مجاني</div>
                <?php endif; ?>

                <a href="index.php?page=book_details&id=<?= $book['id'] ?>" class="block relative aspect-[3/4] bg-gray-50 overflow-hidden border-b border-gray-100">
                    <img src="<?= htmlspecialchars($book['thumbnail_url'] ?? 'https://picsum.photos/400/600') ?>" alt="<?= htmlspecialchars($book['title']) ?>" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent opacity-0 group-hover:opacity-100 transition-opacity flex items-end p-4">
                        <span class="text-white text-xs font-bold"><i class="fas fa-eye ml-1"></i> تصفح الكتاب</span>
                    </div>
                </a>

                <div class="p-5 flex flex-col flex-1">
                    <div class="text-[10px] text-gld-600 font-bold mb-1 flex justify-between">
                        <span><i class="fas fa-folder-open ml-1"></i><?= htmlspecialchars($book['category_name'] ?? 'كتاب رقمي') ?></span>
                        <?php if($book['author']): ?><span><i class="fas fa-pen-nib ml-1"></i><?= htmlspecialchars($book['author']) ?></span><?php endif; ?>
                    </div>
                    <a href="index.php?page=book_details&id=<?= $book['id'] ?>" class="no-underline">
                        <h3 class="font-black text-pri-900 text-base mb-2 line-clamp-2 leading-relaxed hover:text-pri-600 transition-colors" style="min-height: 44px;"><?= htmlspecialchars($book['title']) ?></h3>
                    </a>
                    
                    <div class="flex items-center justify-between pt-4 border-t border-gray-50 mt-auto">
                        <div class="font-black text-pri-700 text-lg">
                            <?= $isFree ? '<span class="text-green-600">مجاني</span>' : number_format($book['price'], 2) . ' <span class="text-xs">ر.س</span>' ?>
                        </div>
                        
                        <?php if ($canDownload): ?>
                            <!-- زر التحميل النقي (يظهر للمجاني والمشترى والإدارة) -->
                            <a href="ajax/download_book.php?id=<?= $book['id'] ?>" target="_blank" class="btn btn-sm btn-outline !py-1.5 !px-4 text-xs bg-white text-green-600 border-green-200 hover:bg-green-50 shadow-sm font-bold">
                                <i class="fas fa-download"></i> تحميل
                            </a>
                        <?php elseif ($isPending): ?>
                            <!-- حالة قيد المراجعة للكتب المدفوعة -->
                            <span class="text-[10px] bg-yellow-50 text-yellow-700 border border-yellow-200 px-3 py-1.5 rounded-lg font-bold shadow-sm">
                                <i class="fas fa-hourglass-half animate-spin"></i> مراجعة الدفع
                            </span>
                        <?php else: ?>
                            <!-- الزر المدفوع يضيف الكتاب للسلة -->
                            <button onclick="addToCart('book', <?= $book['id'] ?>)" class="btn btn-sm btn-primary !py-1.5 !px-4 text-xs shadow-sm hover:scale-105 transition-transform">
                                <i class="fas fa-cart-plus"></i> للسلة
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

</div>