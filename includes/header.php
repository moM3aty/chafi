<?php
// مسار الملف: includes/header.php
// تم تحديث جلب الأقسام ليطابق (sort_order) في القاعدة الجديدة

$stmt = $pdo->query("SELECT * FROM categories WHERE is_active = 1 AND show_in_menu = 1 AND parent_id IS NULL ORDER BY sort_order ASC");
$navCategories = $stmt->fetchAll();

$isAdmin = isset($_SESSION['user_role']) && in_array($_SESSION['user_role'], ['Admin', 'SuperAdmin']);
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? $pageTitle . ' - تشافي' : 'تشافي للرقية الشرعية' ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700;800;900&family=Amiri:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="icon" href="../assets/images/logo.jpg">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        pri: { 50:'#eef5ef',100:'#d4e6d6',200:'#a9ccae',300:'#72a87a',400:'#3f834d',500:'#1f6e33',600:'#1a582a',700:'#154622',800:'#12381d',900:'#0a2111',DEFAULT:'#1a582a'},
                        gld: { 50:'#fdf9ed',100:'#faf0c8',200:'#f5e08c',300:'#edcb50',400:'#e4b629',500:'#c8a020',600:'#a67d18',700:'#855d16',800:'#6e4b18',900:'#4a3210',DEFAULT:'#c8a020'},
                        brk: { 50:'#f8f6f4',100:'#ede8e2',200:'#dad1c5',300:'#c2b3a0',400:'#a89279',500:'#967c64',600:'#836753',700:'#6c5345',800:'#5a463c',900:'#4c3c34',DEFAULT:'#5a463c'}
                    },
                    fontFamily: { cairo:['Cairo','sans-serif'], amiri:['Amiri','serif'] }
                }
            }
        }
    </script>
    <link rel="stylesheet" href="assets/css/site.css" />
</head>
<body class="flex flex-col min-h-screen">
    <div class="islamic-bg"></div>

    <header class="main-header" id="mainHeader">
        <div class="top-strip">
            <div class="max-w-7xl mx-auto px-4 py-2 flex items-center justify-between text-white/70 text-xs sm:text-sm font-medium">
                <div class="flex items-center gap-5">
                    <span class="hover:text-gld-400 transition cursor-pointer"><i class="fas fa-phone-alt ml-1.5 text-gld-400"></i> +966 50 000 0000</span>
                    <span class="hidden sm:inline hover:text-gld-400 transition cursor-pointer"><i class="fas fa-envelope ml-1.5 text-gld-400"></i> info@tashafi.net</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-gld-400 font-amiri text-lg">﷽</span>
                </div>
            </div>
        </div>

        <nav class="max-w-7xl mx-auto px-4 py-4 flex items-center justify-between gap-6">
            <a href="index.php?page=home" class="flex items-center gap-3 text-white no-underline shrink-0 group">
                <div>
                    <div class="text-2xl font-black leading-none tracking-tight font-amiri"> <img width="80px" style="border-radius:15px;" src="../assets/images/logo.jpg" alt="تشافي"></div>

                </div>
            </a>
            
            <div class="flex-1 max-w-2xl hidden lg:block">
                <form action="index.php" method="get" class="relative group">
                    <input type="hidden" name="page" value="products">
                    <input type="text" name="q" placeholder="ابحث عن منتجات، باقات، فيديوهات..." class="form-control !bg-white/10 !border-white/20 !text-white placeholder-white/50 focus:!bg-white/20 focus:!border-gld-400/50 backdrop-blur-md">
                    <button type="submit" class="absolute right-4 top-1/2 -translate-y-1/2 w-8 h-8 flex items-center text-white/50 hover:text-gld-400 transition-colors"><i class="fas fa-search"></i></button>
                </form>
            </div>

            <div class="flex items-center gap-3">
                <a href="index.php?page=cart" class="relative w-11 h-11 rounded-xl bg-white/5 text-white/90 flex items-center justify-center hover:bg-white/15 hover:text-gld-400 transition-all border border-white/10 shadow-sm">
                    <i class="fas fa-shopping-cart text-lg"></i>
                    <span class="absolute -top-1.5 -right-1.5 w-5 h-5 rounded-full bg-gld-500 text-pri-900 text-[10px] font-black flex items-center justify-center shadow-md border-2 border-pri-900" id="cCount"><?= isset($_SESSION['cart']) ? array_sum($_SESSION['cart']) : 0 ?></span>
                </a>
                
                <?php if (isset($_SESSION['user_id'])): ?>
                    <?php if ($isAdmin): ?>
                        <a href="index.php?page=admin_dashboard" class="hidden sm:flex items-center gap-2 py-2.5 px-5 rounded-xl bg-gld-500 text-pri-900 text-sm font-bold hover:bg-gld-400 transition-all">
                            <i class="fas fa-cog text-base"></i><span>الإدارة</span>
                        </a>
                    <?php endif; ?>
                    <a href="index.php?page=dashboard" class="hidden sm:flex items-center gap-2 py-2.5 px-5 rounded-xl bg-gradient-to-r from-pri-500 to-pri-600 text-white text-sm font-bold hover:shadow-lg hover:shadow-pri-500/30 transition-all border border-pri-400">
                        <i class="fas fa-user-circle text-base"></i><span>حسابي</span>
                    </a>
                <?php else: ?>
                    <button onclick="openMdl('authMdl')" class="hidden sm:flex items-center gap-2 py-2.5 px-5 rounded-xl bg-white/10 text-white text-sm font-bold hover:bg-white/20 transition-all border border-white/20 backdrop-blur-sm">
                        <i class="fas fa-sign-in-alt text-base text-gld-400"></i><span>دخول / تسجيل</span>
                    </button>
                <?php endif; ?>
            </div>
        </nav>
        
        <div class="border-t border-white/10 bg-black/10 backdrop-blur-md">
            <div class="max-w-7xl mx-auto px-4 flex items-center gap-2 overflow-x-auto py-2.5 text-sm no-sb">
                <a href="index.php?page=home" class="flex items-center gap-1.5 px-3 py-1 rounded-lg text-white/60 hover:text-white hover:bg-white/8 transition whitespace-nowrap text-xs font-medium no-underline"><i class="fas fa-home text-gld-400 text-[10px]"></i>الرئيسية</a>
                <a href="index.php?page=products" class="flex items-center gap-1.5 px-3 py-1 rounded-lg text-white/60 hover:text-white hover:bg-white/8 transition whitespace-nowrap text-xs font-medium no-underline"><i class="fas fa-box text-gld-400 text-[10px]"></i>كل المنتجات</a>
                <?php foreach($navCategories as $cat): ?>
                    <a href="index.php?page=products&category_id=<?= $cat['id'] ?>" class="flex items-center gap-1.5 px-3 py-1 rounded-lg text-white/60 hover:text-white hover:bg-white/8 transition whitespace-nowrap text-xs font-medium no-underline">
                        <i class="<?= htmlspecialchars($cat['icon_class'] ?? 'fas fa-folder') ?> text-gld-400 text-[10px]"></i><?= htmlspecialchars($cat['name']) ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </header>

    <main class="flex-1 relative z-10 w-full" id="app">