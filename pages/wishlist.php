<?php
// مسار الملف: pages/wishlist.php
// المكان: داخل مجلد pages

if (!isset($_SESSION['user_id'])) {
    echo "<div class='text-center py-20'><h1 class='text-2xl font-bold mb-4'>يجب تسجيل الدخول لرؤية المفضلة</h1><button onclick=\"openMdl('authMdl')\" class='cf-btn cf-btn-pri'>تسجيل الدخول</button></div>";
    return;
}

$userId = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['remove_id'])) {
    $removeId = (int)$_POST['remove_id'];
    $pdo->prepare("DELETE FROM wishlists WHERE id = ? AND user_id = ?")->execute([$removeId, $userId]);
    echo "<script>window.location.href='index.php?page=wishlist';</script>"; exit;
}

$stmt = $pdo->prepare("SELECT w.id as wishlist_id, p.* FROM wishlists w JOIN products p ON w.wishlistable_id = p.id WHERE w.user_id = ? AND w.wishlistable_type = 'product' ORDER BY w.created_at DESC");
$stmt->execute([$userId]);
$wishlistItems = $stmt->fetchAll();
?>

<div class="max-w-7xl mx-auto px-4 py-10 mb-14">
    <div class="flex items-center gap-3 mb-8">
        <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-red-400 to-red-600 flex items-center justify-center text-white text-xl shadow-lg">
            <i class="fas fa-heart"></i>
        </div>
        <h1 class="text-3xl font-black text-pri-900 font-amiri">المفضلة</h1>
    </div>

    <?php if (empty($wishlistItems)): ?>
        <div class="bg-white rounded-3xl border-2 border-border p-12 text-center shadow-sm">
            <i class="far fa-heart text-6xl text-brk-300 mb-4 opacity-50"></i>
            <h3 class="text-xl font-bold text-pri-900 mb-2">قائمة المفضلة فارغة</h3>
            <p class="text-brk-400 mb-6">تصفح المنتجات وأضف ما يعجبك للرجوع إليه لاحقاً.</p>
            <a href="index.php" class="cf-btn cf-btn-pri inline-flex"><i class="fas fa-store"></i> تصفح المتجر</a>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            <?php foreach ($wishlistItems as $prod): ?>
                <div class="prod-card flex flex-col h-full bg-white relative">
                    <form method="post" class="absolute top-2 right-2 z-10">
                        <input type="hidden" name="remove_id" value="<?= $prod['wishlist_id'] ?>" />
                        <button type="submit" class="w-8 h-8 bg-white/90 rounded-full text-red-500 hover:text-red-700 hover:bg-white shadow flex items-center justify-center transition" title="إزالة">
                            <i class="fas fa-times"></i>
                        </button>
                    </form>
                    <div class="h-48 overflow-hidden bg-gray-50 shrink-0">
                        <img src="<?= htmlspecialchars($prod['image_url']) ?>" class="w-full h-full object-cover">
                    </div>
                    <div class="p-4 flex flex-col flex-1">
                        <h4 class="font-bold text-pri-900 text-sm mb-2 line-clamp-2"><?= htmlspecialchars($prod['name']) ?></h4>
                        <div class="mt-auto flex items-center justify-between">
                            <span class="text-pri-700 font-black text-lg"><?= number_format($prod['price'], 2) ?> ر.س</span>
                            <button onclick="addToCart(<?= $prod['id'] ?>, 1, 0)" class="cf-btn cf-btn-pri py-1.5 px-3 text-xs"><i class="fas fa-cart-plus"></i></button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>