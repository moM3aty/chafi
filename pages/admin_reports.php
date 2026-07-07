<?php
// مسار الملف: pages/admin_reports.php

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['Admin', 'SuperAdmin'])) {
    echo "<script>window.location.href='index.php';</script>"; exit;
}

// 1. إحصائيات عامة
$totalRevenue = $pdo->query("SELECT COALESCE(SUM(total_amount),0) FROM orders WHERE status NOT IN ('Cancelled','Refunded')")->fetchColumn();
$monthRevenue = $pdo->query("SELECT COALESCE(SUM(total_amount),0) FROM orders WHERE status NOT IN ('Cancelled','Refunded') AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)")->fetchColumn();
$totalOrders = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
$pendingOrders = $pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'Pending'")->fetchColumn();
$totalUsers = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$newUsersMonth = $pdo->query("SELECT COUNT(*) FROM users WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)")->fetchColumn();
$totalProducts = $pdo->query("SELECT COUNT(*) FROM products WHERE is_active = 1")->fetchColumn();
$lowStock = $pdo->query("SELECT COUNT(*) FROM products WHERE stock_quantity <= low_stock_threshold AND manage_stock = 1 AND is_active = 1")->fetchColumn();
$avgOrderValue = $totalOrders > 0 ? $totalRevenue / $totalOrders : 0;

// 2. أفضل المنتجات مبيعاً
$topProducts = $pdo->query("SELECT p.name, p.price, p.sales_count, p.image_url FROM products p WHERE p.is_active = 1 ORDER BY p.sales_count DESC LIMIT 5")->fetchAll();

// 3. إيرادات آخر 7 أيام
$dailyRevenue = $pdo->query("
    SELECT DATE(created_at) as day, SUM(total_amount) as revenue, COUNT(*) as orders_count
    FROM orders
    WHERE status NOT IN ('Cancelled','Refunded') AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
    GROUP BY DATE(created_at)
    ORDER BY day DESC
")->fetchAll();

// 4. الطلبات حسب الحالة
$statusBreakdown = $pdo->query("
    SELECT status, COUNT(*) as count, SUM(total_amount) as total
    FROM orders
    GROUP BY status
    ORDER BY count DESC
")->fetchAll();

// 5. المنتجات نفاد المخزون
$lowStockProducts = $pdo->query("SELECT id, name, stock_quantity, low_stock_threshold FROM products WHERE stock_quantity <= low_stock_threshold AND manage_stock = 1 AND is_active = 1 ORDER BY stock_quantity ASC LIMIT 10")->fetchAll();
?>

<div class="max-w-7xl mx-auto px-4 py-8 mb-14 afiu">
    <div class="flex items-center justify-between mb-8 gap-4">
        <h1 class="text-2xl font-black text-pri-900 font-amiri"><i class="fas fa-chart-bar text-gld-500 ml-2"></i>التقارير والإحصائيات</h1>
        <a href="index.php?page=admin_dashboard" class="cf-btn cf-btn-out cf-btn-sm bg-white"><i class="fas fa-arrow-right"></i> لوحة القيادة</a>
    </div>

    <!-- بطاقات ملخص سريع -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <div class="erp-card p-5 bg-gradient-to-br from-pri-50 to-white border-pri-100">
            <div class="text-xs text-brk-400 mb-1">إجمالي المبيعات</div>
            <div class="text-2xl font-black text-pri-700"><?= number_format($totalRevenue, 0) ?> <span class="text-sm">ر.س</span></div>
            <div class="text-xs text-green-600 font-bold mt-1">هذا الشهر: <?= number_format($monthRevenue, 0) ?> ر.س</div>
        </div>
        <div class="erp-card p-5 bg-gradient-to-br from-gld-50 to-white border-gld-100">
            <div class="text-xs text-brk-400 mb-1">متوسط قيمة الطلب</div>
            <div class="text-2xl font-black text-gld-700"><?= number_format($avgOrderValue, 2) ?> <span class="text-sm">ر.س</span></div>
            <div class="text-xs text-brk-400 mt-1">إجمالي الطلبات: <?= $totalOrders ?></div>
        </div>
        <div class="erp-card p-5 bg-gradient-to-br from-blue-50 to-white border-blue-100">
            <div class="text-xs text-brk-400 mb-1">العملاء</div>
            <div class="text-2xl font-black text-blue-600"><?= $totalUsers ?></div>
            <div class="text-xs text-green-600 font-bold mt-1">جددوا هذا الشهر: +<?= $newUsersMonth ?></div>
        </div>
        <div class="erp-card p-5 <?= $lowStock > 0 ? 'bg-gradient-to-br from-red-50 to-white border-red-100' : 'bg-gradient-to-br from-green-50 to-white border-green-100' ?>">
            <div class="text-xs text-brk-400 mb-1">حالة المخزون</div>
            <div class="text-2xl font-black <?= $lowStock > 0 ? 'text-red-600' : 'text-green-600' ?>"><?= $lowStock > 0 ? $lowStock . ' منتج منخفض' : 'مخزون جيد' ?></div>
            <div class="text-xs text-brk-400 mt-1">إجمالي المنتجات: <?= $totalProducts ?></div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
        <!-- إيرادات آخر 7 أيام -->
        <div class="erp-card p-6">
            <h3 class="font-bold text-pri-900 mb-5 border-b border-gray-100 pb-3"><i class="fas fa-chart-line text-gld-500 ml-1"></i> إيرادات آخر 7 أيام</h3>
            <?php if(empty($dailyRevenue)): ?>
                <div class="text-center py-8 text-brk-400">لا توجد بيانات</div>
            <?php else: ?>
                <div class="space-y-3">
                    <?php foreach(array_reverse($dailyRevenue) as $d): ?>
                        <div class="flex items-center gap-4">
                            <div class="w-20 text-xs text-brk-400 font-bold" dir="ltr"><?= date('D', strtotime($d['day'])) ?><br><span class="text-pri-900"><?= date('m/d', strtotime($d['day'])) ?></span></div>
                            <div class="flex-1 bg-gray-100 rounded-full h-8 overflow-hidden relative">
                                <?php
                                    $maxRev = max(array_column($dailyRevenue, 'revenue')) ?: 1;
                                    $pct = ($d['revenue'] / $maxRev) * 100;
                                ?>
                                <div class="h-full bg-gradient-to-l from-pri-500 to-pri-400 rounded-full transition-all duration-500 flex items-center justify-end px-3" style="width:<?= max($pct, 5) ?>%">
                                    <span class="text-white text-xs font-bold whitespace-nowrap"><?= number_format($d['revenue'], 0) ?></span>
                                </div>
                            </div>
                            <div class="text-xs text-brk-400 w-10 text-center"><?= $d['orders_count'] ?> طلب</div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- الطلبات حسب الحالة -->
        <div class="erp-card p-6">
            <h3 class="font-bold text-pri-900 mb-5 border-b border-gray-100 pb-3"><i class="fas fa-chart-pie text-gld-500 ml-1"></i> توزيع الطلبات حسب الحالة</h3>
            <?php if(empty($statusBreakdown)): ?>
                <div class="text-center py-8 text-brk-400">لا توجد بيانات</div>
            <?php else: ?>
                <div class="space-y-4">
                    <?php
                        $statusColors = [
                            'Pending' => 'bg-yellow-100 text-yellow-700',
                            'Processing' => 'bg-blue-100 text-blue-700',
                            'Shipped' => 'bg-purple-50 text-purple-600',
                            'Delivered' => 'bg-green-100 text-green-700',
                            'Cancelled' => 'bg-red-100 text-red-700',
                            'Refunded' => 'bg-gray-100 text-gray-600',
                            'Failed' => 'bg-red-50 text-red-500'
                        ];
                        $statusAr = [
                            'Pending' => 'قيد الانتظار',
                            'Processing' => 'قيد التجهيز',
                            'Shipped' => 'تم الشحن',
                            'Delivered' => 'تم التسليم',
                            'Cancelled' => 'ملغي',
                            'Refunded' => 'مسترد',
                            'Failed' => 'فاشل'
                        ];
                        $maxCount = max(array_column($statusBreakdown, 'count')) ?: 1;
                    ?>
                    <?php foreach($statusBreakdown as $s): ?>
                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <span class="badge <?= $statusColors[$s['status']] ?? 'bg-gray-100 text-gray-600' ?>"><?= $statusAr[$s['status']] ?? $s['status'] ?></span>
                                <div class="text-sm"><span class="font-black text-pri-900"><?= $s['count'] ?></span> طلب — <span class="font-bold text-pri-600"><?= number_format($s['total'], 0) ?> ر.س</span></div>
                            </div>
                            <div class="bg-gray-100 rounded-full h-3 overflow-hidden">
                                <div class="h-full rounded-full transition-all duration-500 <?= $s['status'] == 'Delivered' ? 'bg-green-500' : ($s['status'] == 'Cancelled' ? 'bg-red-400' : 'bg-pri-400') ?>" style="width:<?= ($s['count'] / $maxCount) * 100 ?>%"></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- أفضل المنتجات -->
        <div class="erp-card p-6">
            <h3 class="font-bold text-pri-900 mb-5 border-b border-gray-100 pb-3"><i class="fas fa-trophy text-gld-500 ml-1"></i> أفضل 5 منتجات مبيعاً</h3>
            <?php if(empty($topProducts)): ?>
                <div class="text-center py-8 text-brk-400">لا توجد منتجات</div>
            <?php else: ?>
                <div class="space-y-3">
                    <?php foreach($topProducts as $i => $p): ?>
                        <div class="flex items-center gap-4 p-3 rounded-xl <?= $i == 0 ? 'bg-gld-50 border border-gld-200' : 'bg-gray-50' ?>">
                            <div class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-black <?= $i == 0 ? 'bg-gld-500 text-white' : 'bg-gray-200 text-brk-500' ?>"><?= $i + 1 ?></div>
                            <img src="<?= htmlspecialchars($p['image_url'] ?? 'https://picsum.photos/80') ?>" class="w-10 h-10 rounded-lg object-cover border border-gray-200">
                            <div class="flex-1 min-w-0">
                                <div class="font-bold text-pri-900 text-sm truncate"><?= htmlspecialchars($p['name']) ?></div>
                                <div class="text-[11px] text-brk-400"><?= number_format($p['price'], 2) ?> ر.س</div>
                            </div>
                            <div class="text-left">
                                <div class="font-black text-pri-700"><?= $p['sales_count'] ?></div>
                                <div class="text-[10px] text-brk-400">مبيع</div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- تنبيهات المخزون -->
        <div class="erp-card p-6">
            <h3 class="font-bold text-pri-900 mb-5 border-b border-gray-100 pb-3"><i class="fas fa-exclamation-triangle text-red-500 ml-1"></i> تنبيهات المخزون المنخفض</h3>
            <?php if(empty($lowStockProducts)): ?>
                <div class="text-center py-8 text-green-600"><i class="fas fa-check-circle text-3xl mb-2 block"></i>جميع المنتجات بمخزون كافٍ</div>
            <?php else: ?>
                <div class="space-y-3">
                    <?php foreach($lowStockProducts as $p): ?>
                        <div class="flex items-center justify-between p-3 rounded-xl bg-red-50 border border-red-100">
                            <div>
                                <div class="font-bold text-pri-900 text-sm"><?= htmlspecialchars($p['name']) ?></div>
                                <div class="text-[11px] text-brk-400">الحد الأدنى: <?= $p['low_stock_threshold'] ?></div>
                            </div>
                            <div class="text-left">
                                <div class="font-black text-red-600 text-lg"><?= $p['stock_quantity'] ?></div>
                                <div class="text-[10px] text-red-400">بالمخزون</div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>