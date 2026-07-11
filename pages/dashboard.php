<?php
// مسار الملف: pages/dashboard.php
// النسخة الاحترافية — ملف شخصي متكامل

if (!isset($_SESSION['user_id'])) {
    echo "<script>window.location.href='index.php';</script>"; exit;
}

$userId = $_SESSION['user_id'];

// ═══ بيانات المستخدم الحالي ═══
$stmtUser = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmtUser->execute([$userId]);
$user = $stmtUser->fetch();

// ═══ عناوين المستخدم ═══
$userAddresses = $pdo->prepare("SELECT * FROM user_addresses WHERE user_id = ? ORDER BY is_default DESC, id DESC");
$userAddresses->execute([$userId]);
$addresses = $userAddresses->fetchAll();

// ═══ الطلبات ═══
$stmtOrders = $pdo->prepare("
    SELECT o.*, 
           (SELECT COUNT(*) FROM order_items oi WHERE oi.order_id = o.id) as items_count
    FROM orders o 
    WHERE o.user_id = ? 
    ORDER BY o.id DESC
");
$stmtOrders->execute([$userId]);
$orders = $stmtOrders->fetchAll();

// ═══ مواعيد الجلسات المباشرة ═══
$stmtAppts = $pdo->prepare("SELECT * FROM appointments WHERE email = ? ORDER BY id DESC");
$stmtAppts->execute([$user['email']]);
$appointments = $stmtAppts->fetchAll();

// ═══ الاستشارات والرسائل ═══
$stmtMyMsgs = $pdo->prepare("SELECT * FROM contact_messages WHERE user_id = ? ORDER BY created_at DESC");
$stmtMyMsgs->execute([$userId]);
$myMessages = $stmtMyMsgs->fetchAll();
$msgsCount = count($myMessages);

// ═══ المفضلة ═══
$wishlistCount = $pdo->prepare("SELECT COUNT(*) FROM wishlists WHERE user_id = ?");
$wishlistCount->execute([$userId]);
$wishlistCount = $wishlistCount->fetchColumn();

// ═══ الإشعارات ═══
$notifCount = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
$notifCount->execute([$userId]);
$notifCount = $notifCount->fetchColumn();

$ordersCount = count($orders);
$totalSpent = array_sum(array_column($orders, 'total_amount'));

// معالجة تغيير العنوان الافتراضي
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['set_default_address'])) {
    $addrId = (int)$_POST['set_default_address'];
    $pdo->prepare("UPDATE user_addresses SET is_default = 0 WHERE user_id = ?")->execute([$userId]);
    $pdo->prepare("UPDATE user_addresses SET is_default = 1 WHERE id = ? AND user_id = ?")->execute([$addrId, $userId]);
    echo "<script>window.location.href='index.php?page=dashboard';</script>"; exit;
}

// معالجة إضافة عنوان (Fallback لو لم يعمل الـ AJAX)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['addr_city'])) {
    $label = $_POST['addr_label'];
    $phone = $_POST['addr_phone'];
    $city = $_POST['addr_city'];
    $district = $_POST['addr_district'];
    $street = $_POST['addr_street'];
    $landmark = $_POST['addr_landmark'];
    $isDefault = isset($_POST['is_default']) ? 1 : 0;
    
    if($isDefault) {
        $pdo->prepare("UPDATE user_addresses SET is_default = 0 WHERE user_id = ?")->execute([$userId]);
    }
    
    $stmt = $pdo->prepare("INSERT INTO user_addresses (user_id, address_label, full_name, phone, city, district, street, landmark, is_default) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$userId, $label, $user['full_name'], $phone, $city, $district, $street, $landmark, $isDefault]);
    echo "<script>window.location.href='index.php?page=dashboard';</script>"; exit;
}
?>

<div class="max-w-6xl mx-auto px-4 py-8 mb-14">

    <!-- رأس الصفحة -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-8 gap-4 afiu">
        <div class="flex items-center gap-4">
            <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-pri-500 to-pri-800 flex items-center justify-center text-gld-400 text-2xl shadow-lg relative group hover:scale-105 transition-transform">
                <i class="fas fa-user"></i>
                <div class="absolute -bottom-1 -right-1 w-5 h-5 bg-gld-500 text-pri-900 text-[10px] rounded-full flex items-center justify-center font-black">✓</div>
            </div>
            <div>
                <h1 class="text-2xl font-black text-pri-900 font-amiri">مرحباً بك <?= htmlspecialchars($_SESSION['user_name']) ?></h1>
                <p class="text-brk-400 text-sm"><?= htmlspecialchars($user['email']) ?></p>
            </div>
        </div>
        <div class="flex gap-2 flex-wrap">
            <?php if($notifCount > 0): ?>
                <a href="index.php?page=notifications" class="relative cf-btn cf-btn-pri cf-btn-sm bg-white text-xs">
                    <i class="fas fa-bell"></i> الإشعارات
                    <span class="absolute -top-2 -right-2 w-5 h-5 bg-red-500 text-white text-[10px] rounded-full flex items-center justify-center font-black"><?= $notifCount ?></span>
                </a>
            <?php endif; ?>
            <a href="index.php?page=wishlist" class="cf-btn cf-btn-out cf-btn-sm bg-white text-xs relative">
                <i class="fas fa-heart"></i> المفضلة
                <?php if($wishlistCount > 0): ?><span class="absolute -top-2 -right-2 w-5 h-5 bg-red-500 text-white text-[10px] rounded-full flex items-center justify-center font-black"><?= $wishlistCount ?></span><?php endif; ?>
            </a>
            <a href="ajax/logout.php" class="cf-btn cf-btn-out cf-btn-sm bg-white text-red-500 border-red-200 hover:!bg-red-500 hover:!text-white text-xs">
                <i class="fas fa-sign-out-alt"></i> خروج
            </a>
        </div>
    </div>

    <!-- بطاقات سريعة -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-8 afiu" style="animation-delay:.08s">
        <button type="button" onclick="switchDashTab('orders')" class="dash-mini-stat no-underline hover:border-pri-300 group">
            <div class="w-11 h-11 rounded-xl bg-pri-100 text-pri-600 flex items-center justify-center text-lg group-hover:scale-110 transition-transform"><i class="fas fa-box"></i></div>
            <span class="font-black text-pri-900"><?= $ordersCount ?></span>
            <span class="text-[10px] text-brk-400">الطلبات</span>
        </button>
        <button type="button" onclick="switchDashTab('appointments')" class="dash-mini-stat no-underline hover:border-purple-300 group">
            <div class="w-11 h-11 rounded-xl bg-purple-100 text-purple-600 flex items-center justify-center text-lg group-hover:scale-110 transition-transform"><i class="fas fa-video"></i></div>
            <span class="font-black text-pri-900"><?= count($appointments) ?></span>
            <span class="text-[10px] text-brk-400">جلسات الرقية</span>
        </button>
        <button type="button" onclick="switchDashTab('messages')" class="dash-mini-stat no-underline hover:border-blue-300 group">
            <div class="w-11 h-11 rounded-xl bg-blue-50 text-blue-500 flex items-center justify-center text-lg group-hover:scale-110 transition-transform"><i class="fas fa-envelope-open-text"></i></div>
            <span class="font-black text-pri-900"><?= $msgsCount ?></span>
            <span class="text-[10px] text-brk-400">الاستشارات</span>
        </button>
        <div class="dash-mini-stat no-underline !cursor-default">
            <div class="w-11 h-11 rounded-xl bg-gld-100 text-gld-700 flex items-center justify-center text-lg"><i class="fas fa-wallet"></i></div>
            <span class="font-black text-pri-900"><?= number_format($totalSpent, 0) ?></span>
            <span class="text-[10px] text-brk-400">إجمالي مشتريات</span>
        </div>
    </div>

    <!-- التبويبات الشاملة الخمسة -->
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden mb-8 afiu" style="animation-delay:.12s">
        <div class="flex overflow-x-auto no-sb border-b border-gray-100 bg-gray-50/50">
            <button type="button" onclick="switchDashTab('orders')" id="dtab-orders" class="dash-dtab on flex-1 min-w-[120px] py-4 text-center font-bold text-sm transition-colors text-pri-700 bg-pri-50 border-b-[3px] border-pri-500">
                <i class="fas fa-shopping-bag ml-1"></i> الطلبات
            </button>
            <button type="button" onclick="switchDashTab('appointments')" id="dtab-appointments" class="dash-dtab flex-1 min-w-[120px] py-4 text-center font-bold text-sm transition-colors text-brk-400 border-b-[3px] border-transparent hover:bg-white">
                <i class="fas fa-video ml-1"></i> جلسات الرقية
            </button>
            <button type="button" onclick="switchDashTab('messages')" id="dtab-messages" class="dash-dtab flex-1 min-w-[120px] py-4 text-center font-bold text-sm transition-colors text-brk-400 border-b-[3px] border-transparent hover:bg-white">
                <i class="fas fa-comments ml-1"></i> الاستشارات
            </button>
            <button type="button" onclick="switchDashTab('addresses')" id="dtab-addresses" class="dash-dtab flex-1 min-w-[120px] py-4 text-center font-bold text-sm transition-colors text-brk-400 border-b-[3px] border-transparent hover:bg-white">
                <i class="fas fa-map-marker-alt ml-1"></i> العناوين
            </button>
            <button type="button" onclick="switchDashTab('profile')" id="dtab-profile" class="dash-dtab flex-1 min-w-[120px] py-4 text-center font-bold text-sm transition-colors text-brk-400 border-b-[3px] border-transparent hover:bg-white">
                <i class="fas fa-user-cog ml-1"></i> بياناتي
            </button>
        </div>

        <!-- ═══ تبويب: سجل الطلبات ═══ -->
        <div id="dpanel-orders" class="dash-dpanel block p-6">
            <?php if (empty($orders)): ?>
                <div class="text-center py-14 text-brk-400 bg-gray-50 rounded-2xl border border-dashed border-gray-200">
                    <i class="fas fa-box-open text-5xl mb-4 block opacity-20"></i>
                    <h3 class="text-xl font-bold text-pri-900 mb-2">لا توجد طلبات حتى الآن</h3>
                    <a href="index.php?page=products" class="btn btn-primary mt-4"><i class="fas fa-store"></i> تصفح المتجر</a>
                </div>
            <?php else: ?>
                <div class="space-y-4">
                    <?php 
                    $statusAr = ['Pending'=>'قيد المراجعة','Processing'=>'قيد التجهيز','Shipped'=>'تم الشحن','Delivered'=>'تم التسليم','Cancelled'=>'ملغي','Refunded'=>'مسترد','Failed'=>'فاشل'];
                    $statusClr = ['Pending'=>'bg-yellow-50 text-yellow-700 border-yellow-200','Processing'=>'bg-blue-50 text-blue-700 border-blue-200','Shipped'=>'bg-purple-50 text-purple-700 border-purple-200','Delivered'=>'bg-green-50 text-green-700 border-green-200','Cancelled'=>'bg-red-50 text-red-700 border-red-200','Refunded'=>'bg-gray-50 text-gray-600 border-gray-200','Failed'=>'bg-red-50 text-red-600 border-red-200'];
                    foreach ($orders as $o):
                    ?>
                    <div class="bg-white rounded-2xl border border-gray-100 p-5 hover:border-pri-200 hover:shadow-md transition-all group">
                        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                            <div class="flex items-center gap-4 min-w-0">
                                <div class="w-12 h-12 rounded-xl bg-pri-50 text-pri-600 flex items-center justify-center text-lg font-bold shrink-0 shadow-sm"><i class="fas fa-shopping-bag"></i></div>
                                <div class="min-w-0">
                                    <div class="font-bold text-pri-900 text-base mb-1" dir="ltr">#<?= $o['order_number'] ?></div>
                                    <div class="text-xs text-brk-500 flex items-center gap-2"><i class="far fa-clock"></i> <?= date('Y-m-d H:i', strtotime($o['created_at'])) ?></div>
                                </div>
                            </div>
                            <div class="flex items-center justify-between md:justify-end gap-5 shrink-0 bg-gray-50 md:bg-transparent p-3 md:p-0 rounded-xl">
                                <div class="text-right">
                                    <div class="text-[10px] text-brk-400 mb-0.5">الإجمالي</div>
                                    <div class="font-black text-pri-700 text-lg leading-none"><?= number_format($o['total_amount'], 2) ?> <span class="text-xs">ر.س</span></div>
                                </div>
                                <div class="w-px h-8 bg-gray-200 hidden md:block"></div>
                                <div class="text-right">
                                    <div class="text-[10px] text-brk-400 mb-1">حالة الطلب</div>
                                    <span class="badge <?= $statusClr[$o['status']] ?? 'bg-gray-100 text-gray-600' ?> !text-xs px-3 py-1 shadow-sm"><?= $statusAr[$o['status']] ?? $o['status'] ?></span>
                                </div>
                                <a href="index.php?page=admin_order_details&id=<?= $o['id'] ?>" class="btn btn-sm btn-outline bg-white !p-2.5 shrink-0 shadow-sm" title="عرض الفاتورة">
                                    <i class="fas fa-file-invoice"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- ═══ التعديل هنا: تبويب مواعيدي وجلساتي ═══ -->
        <div id="dpanel-appointments" class="dash-dpanel hidden p-6 bg-gray-50/30">
            <?php if (empty($appointments)): ?>
                <div class="text-center py-14 text-brk-400 bg-white rounded-2xl border border-dashed border-gray-200 shadow-sm">
                    <i class="fas fa-video-slash text-5xl mb-4 block opacity-20"></i>
                    <h3 class="text-xl font-bold text-pri-900 mb-2">ليس لديك أي جلسات رقية محجوزة</h3>
                    <p class="text-sm mb-6">يمكنك حجز جلسة تشخيص ورقية مباشرة مع الشيخ أونلاين براحة وسرية تامة.</p>
                    <a href="index.php?page=book_appointment" class="btn btn-gold shadow-lg hover:scale-105 transition-transform"><i class="fas fa-calendar-plus"></i> احجز موعد الآن</a>
                </div>
            <?php else: ?>
                <div class="flex items-center justify-between mb-6 border-b border-gray-200 pb-4">
                    <h3 class="font-black text-pri-900 text-lg"><i class="fas fa-video text-gld-500 ml-2"></i> جلسات الرقية (أونلاين)</h3>
                    <a href="index.php?page=book_appointment" class="btn btn-sm btn-gold"><i class="fas fa-plus"></i> حجز جديد</a>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
                    <?php foreach ($appointments as $apt): 
                        $aptStatus = [
                            'Pending' => ['text'=>'قيد المراجعة والتأكيد', 'color'=>'bg-yellow-50 text-yellow-700 border-yellow-200', 'icon'=>'fa-hourglass-half'],
                            'Confirmed' => ['text'=>'مؤكد - جاهز للاتصال', 'color'=>'bg-green-50 text-green-700 border-green-200', 'icon'=>'fa-check-circle'],
                            'Completed' => ['text'=>'تم الانتهاء', 'color'=>'bg-gray-50 text-gray-600 border-gray-200', 'icon'=>'fa-check-double'],
                            'Cancelled' => ['text'=>'ملغي', 'color'=>'bg-red-50 text-red-700 border-red-200', 'icon'=>'fa-times-circle'],
                        ][$apt['status']] ?? ['text'=>$apt['status'], 'color'=>'bg-gray-100', 'icon'=>'fa-info'];
                    ?>
                    <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden relative shadow-sm hover:shadow-md transition-all group">
                        <div class="absolute right-0 top-0 bottom-0 w-1.5 <?= strpos($aptStatus['color'], 'green')!==false ? 'bg-green-500' : (strpos($aptStatus['color'], 'yellow')!==false ? 'bg-yellow-400' : 'bg-gray-300') ?>"></div>
                        
                        <div class="p-5 pr-6 flex flex-col h-full">
                            <div class="flex items-start justify-between gap-2 mb-4">
                                <div>
                                    <h4 class="font-bold text-pri-900 text-base mb-1">جلسة تشخيص ورقية</h4>
                                    <div class="text-[10px] text-brk-400 font-mono" dir="ltr">#APT-<?= $apt['id'] ?></div>
                                </div>
                                <span class="badge <?= $aptStatus['color'] ?> border px-2.5 py-1 text-xs shrink-0 shadow-sm">
                                    <i class="fas <?= $aptStatus['icon'] ?>"></i> <?= $aptStatus['text'] ?>
                                </span>
                            </div>
                            
                            <div class="space-y-2 text-sm mb-5 flex-1">
                                <div class="flex gap-2">
                                    <i class="far fa-clock text-brk-300 mt-1"></i>
                                    <div>
                                        <div class="text-xs text-brk-400">وقتك المفضل:</div>
                                        <div class="font-bold text-brk-700"><?= htmlspecialchars($apt['preferred_time']) ?></div>
                                    </div>
                                </div>

                                <!-- التعديل: إظهار الموعد المعتمد من الإدارة -->
                                <?php if ($apt['status'] == 'Confirmed'): ?>
                                <div class="bg-green-50 p-3 rounded-xl border border-green-100 mt-3 flex items-start gap-2 shadow-inner">
                                    <i class="fas fa-calendar-check text-green-500 mt-0.5"></i>
                                    <div>
                                        <div class="text-[10px] font-bold text-green-800 uppercase tracking-wider mb-0.5">موعد الجلسة المعتمد</div>
                                        <div class="font-black text-green-700 text-sm leading-relaxed"><?= htmlspecialchars($apt['scheduled_time'] ?: 'يرجى التواصل عبر واتساب لتحديد الوقت الدقيق') ?></div>
                                    </div>
                                </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="pt-4 border-t border-gray-50 mt-auto">
                                <?php if ($apt['status'] == 'Confirmed'): ?>
                                    <a href="index.php?page=meeting&id=<?= $apt['id'] ?>" class="btn btn-primary btn-block !py-3 animate-[pulse_2s_ease-in-out_infinite] shadow-[0_0_15px_rgba(26,88,42,0.3)] hover:scale-[1.02] transition-transform">
                                        <i class="fas fa-video"></i> الدخول لغرفة الجلسة الآن
                                    </a>
                                <?php elseif ($apt['status'] == 'Pending'): ?>
                                    <div class="bg-yellow-50/50 text-yellow-700 text-xs p-3 rounded-xl text-center font-bold border border-yellow-100">
                                        <i class="fas fa-hourglass-half animate-spin ml-1"></i> بانتظار اعتماد الموعد لفتح الغرفة
                                    </div>
                                <?php else: ?>
                                    <div class="bg-gray-50 text-gray-500 text-xs p-3 rounded-xl text-center font-bold border border-gray-100">
                                        <i class="fas fa-info-circle ml-1"></i> الجلسة غير نشطة حالياً
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- ═══ تبويب: رسائلي واستشاراتي ═══ -->
        <div id="dpanel-messages" class="dash-dpanel hidden p-6 bg-gray-50/30">
            <div class="flex items-center justify-between mb-6 border-b border-gray-200 pb-4">
                <div>
                    <h3 class="font-black text-pri-900 text-lg"><i class="fas fa-comments text-blue-500 ml-2"></i>رسائلي واستشاراتي السابقة</h3>
                    <p class="text-xs text-brk-500 mt-1">تتبع ردود الإدارة (الشيخ) على رسائلك الخاصة.</p>
                </div>
                <a href="index.php?page=contact" class="btn btn-sm btn-gold"><i class="fas fa-plus"></i> استشارة جديدة</a>
            </div>

            <?php if(empty($myMessages)): ?>
                <div class="text-center py-10 bg-white rounded-2xl border border-dashed border-gray-300 shadow-sm">
                    <i class="far fa-comments text-5xl text-brk-200 mb-3 block"></i>
                    <p class="text-sm font-bold text-pri-900">لم تقم بإرسال أي رسائل أو استشارات بعد.</p>
                </div>
            <?php else: ?>
                <div class="space-y-6">
                    <?php foreach($myMessages as $msg): ?>
                        <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden shadow-sm hover:shadow-md transition-shadow">
                            <div class="bg-gray-50 px-5 py-3 border-b border-gray-200 flex justify-between items-center">
                                <div class="font-bold text-pri-900 text-sm"><i class="fas fa-tag text-brk-300 ml-1"></i> <?= htmlspecialchars($msg['subject'] ?: 'استشارة عامة') ?></div>
                                <div class="text-[10px] text-brk-400 bg-white px-2 py-1 rounded border border-gray-100 shadow-sm" dir="ltr"><i class="far fa-calendar-alt"></i> <?= date('Y-m-d H:i', strtotime($msg['created_at'])) ?></div>
                            </div>
                            <div class="p-5 bg-[url('https://www.transparenttextures.com/patterns/cubes.png')]">
                                
                                <!-- رسالة العميل -->
                                <div class="flex gap-3 mb-5">
                                    <div class="w-8 h-8 rounded-full bg-pri-100 text-pri-600 flex items-center justify-center shrink-0 shadow-sm"><i class="fas fa-user text-xs"></i></div>
                                    <div class="bg-white border border-gray-100 p-4 rounded-2xl rounded-tr-none text-sm text-gray-700 leading-relaxed max-w-[85%] shadow-sm">
                                        <?= nl2br(htmlspecialchars($msg['message'])) ?>
                                    </div>
                                </div>

                                <!-- رد الإدارة (الشيخ) -->
                                <?php if(!empty($msg['admin_reply'])): ?>
                                    <div class="flex gap-3 flex-row-reverse">
                                        <div class="w-8 h-8 rounded-full bg-gradient-to-br from-gld-400 to-gld-600 text-white flex items-center justify-center shrink-0 shadow-md"><i class="fas fa-user-shield text-xs"></i></div>
                                        <div class="bg-gradient-to-br from-pri-50 to-white border border-pri-100 p-4 rounded-2xl rounded-tl-none text-sm text-pri-900 font-bold leading-relaxed max-w-[85%] shadow-sm relative">
                                            <?= nl2br(htmlspecialchars($msg['admin_reply'])) ?>
                                            <div class="text-left mt-3 pt-2 border-t border-pri-100/50"><span class="text-[10px] text-pri-500 font-normal bg-pri-50/50 px-2 py-1 rounded"><i class="fas fa-check-double text-green-500"></i> رد الإدارة (تشافي)</span></div>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <div class="flex gap-3 flex-row-reverse opacity-70">
                                        <div class="w-8 h-8 rounded-full bg-gray-200 text-gray-500 flex items-center justify-center shrink-0"><i class="fas fa-clock text-xs"></i></div>
                                        <div class="bg-gray-50 border border-gray-100 p-3 rounded-2xl rounded-tl-none text-xs text-gray-500 italic max-w-[85%] flex items-center gap-2">
                                            <i class="fas fa-circle-notch fa-spin text-gray-400"></i> جاري مراجعة رسالتك وسيتم الرد قريباً...
                                        </div>
                                    </div>
                                <?php endif; ?>

                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- ═══ تبويب: العناوين ═══ -->
        <div id="dpanel-addresses" class="dash-dpanel hidden p-6 bg-gray-50/30">
            <?php if (empty($addresses)): ?>
                <div class="text-center py-14 text-brk-400 bg-white rounded-2xl border border-dashed border-gray-200 shadow-sm">
                    <i class="fas fa-map-marker-alt text-5xl mb-4 block opacity-20"></i>
                    <h3 class="text-xl font-bold text-pri-900 mb-2">لا توجد عناوين محفوظة</h3>
                    <p class="text-sm mb-6">أضف عنوانك الأول لتسهيل وتسريع عملية الطلب مستقبلاً.</p>
                </div>
            <?php else: ?>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <?php foreach ($addresses as $addr): ?>
                    <div class="bg-white rounded-2xl border-2 border-gray-100 p-5 hover:border-pri-300 hover:shadow-md transition-all group relative overflow-hidden">
                        <?php if ($addr['is_default']): ?>
                            <div class="absolute top-0 right-0 w-12 h-12 overflow-hidden">
                                <div class="bg-green-500 text-white text-[8px] font-bold text-center w-16 transform rotate-45 translate-x-[14px] translate-y-[8px] shadow-sm py-0.5">أساسي</div>
                            </div>
                        <?php endif; ?>
                        
                        <div class="flex items-start justify-between mb-4">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-pri-50 text-pri-600 flex items-center justify-center text-lg shadow-sm"><i class="fas fa-map-marked-alt"></i></div>
                                <div>
                                    <div class="font-black text-pri-900 text-base"><?= htmlspecialchars($addr['address_label']) ?></div>
                                    <div class="text-xs text-brk-500 font-bold"><?= htmlspecialchars($addr['full_name']) ?></div>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-2 text-xs text-brk-600 bg-gray-50 rounded-xl p-4 border border-gray-100">
                            <div class="flex items-center gap-2"><i class="fas fa-phone-alt text-brk-300 w-3 text-center"></i> <span dir="ltr" class="font-bold text-pri-700"><?= htmlspecialchars($addr['phone']) ?></span></div>
                            <div class="flex items-center gap-2"><i class="fas fa-map-pin text-brk-300 w-3 text-center"></i> <span><?= htmlspecialchars($addr['city']) ?> <?= $addr['district'] ? ' - ' . htmlspecialchars($addr['district']) : '' ?></span></div>
                            <div class="flex items-start gap-2"><i class="fas fa-street-view text-brk-300 w-3 text-center mt-1"></i> <span class="leading-relaxed"><?= htmlspecialchars($addr['street'] ?? '-') ?></span></div>
                            <?php if ($addr['landmark']): ?>
                                <div class="flex items-start gap-2"><i class="fas fa-map-signs text-brk-300 w-3 text-center mt-1"></i> <span class="leading-relaxed text-gld-700 font-bold"><?= htmlspecialchars($addr['landmark']) ?></span></div>
                            <?php endif; ?>
                        </div>

                        <?php if (!$addr['is_default']): ?>
                        <div class="mt-4 pt-4 border-t border-gray-50 text-left">
                            <form method="post" action="index.php?page=dashboard" class="inline" onsubmit="return confirm('هل تريد جعل هذا العنوان هو الافتراضي لاستلام طلباتك؟')">
                                <input type="hidden" name="set_default_address" value="<?= $addr['id'] ?>">
                                <button type="submit" class="btn btn-sm btn-outline bg-white shadow-sm"><i class="fas fa-star text-gld-500"></i> تعيين كافتراضي</button>
                            </form>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            <div class="mt-6">
                <!-- زر إضافة عنوان جديد -->
                <button onclick="openAddAddress()" class="w-full py-4 rounded-2xl border-2 border-dashed border-pri-300 bg-pri-50/50 text-base font-bold text-pri-700 hover:border-pri-500 hover:bg-pri-50 hover:shadow-md transition-all shadow-inner">
                    <i class="fas fa-plus-circle ml-2 text-lg"></i> إضافة عنوان استلام جديد
                </button>
            </div>
        </div>

        <!-- ═══ تبويب: بيانات الحساب ═══ -->
        <div id="dpanel-profile" class="dash-dpanel hidden p-6 sm:p-10 bg-white">
            <h3 class="text-xl font-black text-pri-900 font-amiri mb-6 border-b border-gray-100 pb-3"><i class="fas fa-user-cog text-gld-500 ml-2"></i>تحديث بيانات الحساب</h3>
            <form method="post" action="#" id="profileForm" onsubmit="updateProfile(event)" class="max-w-2xl">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div class="form-group !mb-0">
                        <label class="form-label">الاسم الكامل <span class="req">*</span></label>
                        <div class="relative">
                            <i class="fas fa-user absolute right-4 top-1/2 -translate-y-1/2 text-brk-300"></i>
                            <input type="text" name="full_name" class="form-control !pr-10" value="<?= htmlspecialchars($user['full_name']) ?>" required>
                        </div>
                    </div>
                    <div class="form-group !mb-0">
                        <label class="form-label">رقم الجوال</label>
                        <div class="relative">
                            <i class="fas fa-phone absolute right-4 top-1/2 -translate-y-1/2 text-brk-300"></i>
                            <input type="tel" name="phone" class="form-control !pr-10" dir="ltr" value="<?= htmlspecialchars($user['phone'] ?? '') ?>" placeholder="05XXXXXXXX">
                        </div>
                    </div>
                </div>

                <div class="form-group mb-8">
                    <label class="form-label">البريد الإلكتروني <span class="req">*</span></label>
                    <div class="relative">
                        <i class="fas fa-envelope absolute right-4 top-1/2 -translate-y-1/2 text-brk-300"></i>
                        <input type="email" name="email" class="form-control !pr-10 bg-gray-50" dir="ltr" value="<?= htmlspecialchars($user['email']) ?>" required readonly title="لا يمكن تغيير البريد الإلكتروني الأساسي من هنا">
                    </div>
                    <p class="text-xs text-brk-400 mt-2"><i class="fas fa-info-circle"></i> لتغيير البريد الإلكتروني يرجى التواصل مع الإدارة.</p>
                </div>

                <button type="submit" id="profileBtn" class="btn btn-primary btn-lg shadow-xl hover:scale-105 transition-transform w-full sm:w-auto px-12">
                    <i class="fas fa-save"></i> حفظ التعديلات
                </button>
            </form>
        </div>
    </div>
</div>

<!-- ═══ نموذج إضافة عنوان جديد (Modal) ═══ -->
<div id="addAddressModal" class="modal-backdrop">
    <div class="modal-dialog" style="max-width:550px">
        <button onclick="closeMdl('addAddressModal')" class="modal-close"><i class="fas fa-times"></i></button>
        <div class="modal-header border-b border-gray-100 pb-4">
            <div class="w-16 h-16 bg-gradient-to-br from-gld-400 to-gld-600 text-white rounded-full flex items-center justify-center text-2xl mx-auto mb-4 shadow-lg"><i class="fas fa-map-marker-alt"></i></div>
            <h2 class="text-2xl font-black text-pri-900 font-amiri">إضافة عنوان جديد</h2>
        </div>
        <div class="modal-body bg-gray-50/50">
            <form method="post" action="index.php?page=dashboard" id="addAddressForm">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-5">
                    <div class="form-group !mb-0">
                        <label class="form-label">مسمى العنوان <span class="req">*</span></label>
                        <input type="text" name="addr_label" class="form-control bg-white" value="المنزل" required placeholder="المنزل، العمل...">
                    </div>
                    <div class="form-group !mb-0">
                        <label class="form-label">رقم الجوال للاستلام <span class="req">*</span></label>
                        <input type="tel" name="addr_phone" class="form-control bg-white" dir="ltr" placeholder="05XXXXXXXX" required>
                    </div>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-5">
                    <div class="form-group !mb-0">
                        <label class="form-label">المدينة <span class="req">*</span></label>
                        <input type="text" name="addr_city" class="form-control bg-white" placeholder="الرياض، جدة..." required>
                    </div>
                    <div class="form-group !mb-0">
                        <label class="form-label">الحي</label>
                        <input type="text" name="addr_district" class="form-control bg-white" placeholder="اسم الحي">
                    </div>
                </div>

                <div class="form-group mb-5">
                    <label class="form-label">الشارع والتفاصيل <span class="req">*</span></label>
                    <textarea name="addr_street" class="form-textarea bg-white" rows="2" placeholder="الشارع، رقم المبنى، الدور..." required></textarea>
                </div>
                
                <div class="form-group mb-5">
                    <label class="form-label">علامة مميزة (اختياري)</label>
                    <input type="text" name="addr_landmark" class="form-control bg-white" placeholder="بجانب مسجد كذا، أو سوبر ماركت...">
                </div>
                
                <div class="flex items-center gap-4 mb-6 pt-5 border-t border-gray-200">
                    <label class="toggle-switch">
                        <input type="checkbox" name="is_default" value="1" checked>
                        <span class="toggle-slider"></span>
                        <span class="mr-3 font-bold text-pri-900">تعيين كعنوان افتراضي</span>
                    </label>
                </div>
                
                <button type="submit" class="btn btn-primary btn-lg btn-block shadow-lg hover:shadow-pri-500/30 transition-shadow">
                    <i class="fas fa-plus-circle"></i> حفظ العنوان
                </button>
            </form>
        </div>
    </div>
</div>

<script>
// التبديل السلس للتبويبات
function switchDashTab(tabName) {
    document.querySelectorAll('.dash-dtab').forEach(el => {
        el.classList.remove('on', 'bg-pri-50', 'text-pri-700', 'border-pri-500');
        el.classList.add('text-brk-400', 'border-transparent');
    });
    
    document.querySelectorAll('.dash-dpanel').forEach(el => el.classList.add('hidden'));
    
    const activeBtn = document.getElementById('dtab-' + tabName);
    if(activeBtn) {
        activeBtn.classList.remove('text-brk-400', 'border-transparent');
        activeBtn.classList.add('on', 'bg-pri-50', 'text-pri-700', 'border-pri-500');
    }
    
    const panel = document.getElementById('dpanel-' + tabName);
    if(panel) {
        panel.classList.remove('hidden');
        panel.classList.add('block');
        panel.style.animation = 'tab-fade .4s ease-out';
    }
}

// فتح نموذج العنوان
function openAddAddress() {
    openMdl('addAddressModal');
}

// تحديث البروفايل عبر AJAX
async function updateProfile(e) {
    e.preventDefault();
    const btn = document.getElementById('profileBtn');
    const origText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> جاري الحفظ...';

    try {
        const form = document.getElementById('profileForm');
        const fd = new FormData(form);

        const res = await fetch('ajax/auth.php?action=update_profile', {
            method: 'POST',
            body: fd
        });

        const result = await res.json();

        if (result.success) {
            showToast(result.message, 'ok');
            setTimeout(() => window.location.reload(), 800);
        } else {
            showToast(result.message, 'err');
            btn.disabled = false;
            btn.innerHTML = origText;
        }
    } catch(err) {
        showToast('حدث خطأ في الاتصال بالخادم', 'err');
        btn.disabled = false;
        btn.innerHTML = origText;
    }
}
</script>