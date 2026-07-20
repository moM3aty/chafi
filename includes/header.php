<?php
// مسار الملف: includes/header.php
$stmt = $pdo->query("SELECT * FROM categories WHERE is_active = 1 AND show_in_menu = 1 AND parent_id IS NULL ORDER BY sort_order ASC");
$navCategories = $stmt->fetchAll();

$cmsStmt = $pdo->query("SELECT * FROM cms_pages WHERE is_active = 1 ORDER BY sort_order ASC");
$navPages = $cmsStmt->fetchAll();

$isAdmin = isset($_SESSION['user_role']) && in_array($_SESSION['user_role'], ['Admin', 'SuperAdmin']);

$currentCurrency = $_SESSION['currency'] ?? 'SAR';

$currencies = [
    'SAR' => ['symbol' => 'ر.س', 'rate' => 1.0, 'flag' => '🇸🇦', 'name' => 'ريال سعودي'],
    'AED' => ['symbol' => 'د.إ', 'rate' => 0.98, 'flag' => '🇦🇪', 'name' => 'درهم إماراتي'],
    'KWD' => ['symbol' => 'د.ك', 'rate' => 0.082, 'flag' => '🇰🇼', 'name' => 'دينار كويتي'],
    'BHD' => ['symbol' => 'د.ب', 'rate' => 0.10, 'flag' => '🇧🇭', 'name' => 'دينار بحريني'],
    'OMR' => ['symbol' => 'ر.ع', 'rate' => 0.10, 'flag' => '🇴🇲', 'name' => 'ريال عماني'],
    'QAR' => ['symbol' => 'ر.ق', 'rate' => 0.97, 'flag' => '🇶🇦', 'name' => 'ريال قطري'],
    'EGP' => ['symbol' => 'ج.م', 'rate' => 12.80, 'flag' => '🇪🇬', 'name' => 'جنيه مصري'],
    'JOD' => ['symbol' => 'د.أ', 'rate' => 0.189, 'flag' => '🇯🇴', 'name' => 'دينار أردني'],
    'MAD' => ['symbol' => 'د.م', 'rate' => 2.65, 'flag' => '🇲🇦', 'name' => 'درهم مغربي'],
    'DZD' => ['symbol' => 'د.ج', 'rate' => 35.80, 'flag' => '🇩🇿', 'name' => 'دينار جزائري'],
    'TND' => ['symbol' => 'د.ت', 'rate' => 0.83, 'flag' => '🇹🇳', 'name' => 'دينار تونسي'],
    'LBP' => ['symbol' => 'ل.ل', 'rate' => 23800, 'flag' => '🇱🇧', 'name' => 'ليرة لبنانية'],
    'IQD' => ['symbol' => 'ع.د', 'rate' => 350.0, 'flag' => '🇮🇶', 'name' => 'دينار عراقي'],
    'TRY' => ['symbol' => '₺', 'rate' => 8.60, 'flag' => '🇹🇷', 'name' => 'ليرة تركية'],
    'USD' => ['symbol' => '$', 'rate' => 0.266, 'flag' => '🇺🇸', 'name' => 'دولار أمريكي'],
    'EUR' => ['symbol' => '€', 'rate' => 0.245, 'flag' => '🇪🇺', 'name' => 'يورو'],
    'GBP' => ['symbol' => '£', 'rate' => 0.210, 'flag' => '🇬🇧', 'name' => 'جنيه إسترليني'],
    'CAD' => ['symbol' => 'CA$', 'rate' => 0.36, 'flag' => '🇨🇦', 'name' => 'دولار كندي'],
    'AUD' => ['symbol' => 'AU$', 'rate' => 0.40, 'flag' => '🇦🇺', 'name' => 'دولار أسترالي'],
    'CHF' => ['symbol' => 'Fr', 'rate' => 0.24, 'flag' => '🇨🇭', 'name' => 'فرنك سويسري']
];

$activeCurrObj = $currencies[$currentCurrency];
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
    <script>
        window.ChafiCurrency = {
            code: '<?= $currentCurrency ?>',
            symbol: '<?= $activeCurrObj['symbol'] ?>',
            rate: <?= $activeCurrObj['rate'] ?>
        };
    </script>
</head>
<body class="flex flex-col min-h-screen">
    <div class="islamic-bg"></div>
    <header class="main-header" id="mainHeader">
        <div class="top-strip relative z-50">
            <div class="max-w-7xl mx-auto px-4 py-2 flex items-center justify-between text-white/70 text-xs sm:text-sm font-medium">
                <div class="flex items-center gap-5">
                    <span class="hover:text-gld-400 transition cursor-pointer" dir="ltr"><i class="fas fa-phone-alt mr-1.5 text-gld-400"></i> +966 53 548 8493</span>
                    <a href="index.php?page=contact" class="hidden sm:flex items-center gap-1.5 hover:text-gld-400 transition cursor-pointer font-bold no-underline">
                        <i class="fas fa-headset text-gld-400"></i> تواصل معنا
                    </a>
                </div>
                <div class="flex items-center gap-4">
                    <div class="relative group">
                        <button class="flex items-center gap-1.5 hover:text-white transition cursor-pointer bg-white/5 border border-white/10 px-3 py-1 rounded-full backdrop-blur-sm">
                            <span class="text-base"><?= $activeCurrObj['flag'] ?></span> 
                            <span class="font-bold font-mono" dir="ltr"><?= $currentCurrency ?></span>
                            <i class="fas fa-chevron-down text-[9px] opacity-70"></i>
                        </button>
                        <div class="absolute left-0 top-full mt-1 w-44 max-h-80 overflow-y-auto no-sb bg-white rounded-xl shadow-xl border border-gray-100 py-2 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 transform origin-top-left z-[9999]">
                            <div class="px-3 pb-2 mb-2 border-b border-gray-50 text-[10px] text-gray-400 font-bold">اختر عملة العرض</div>
                            <?php foreach($currencies as $code => $c): ?>
                                <button onclick="changeCurrency('<?= $code ?>')" class="w-full text-right px-4 py-2 hover:bg-pri-50 text-pri-900 transition flex items-center gap-3 <?= $code == $currentCurrency ? 'bg-pri-50/50 font-bold' : '' ?>">
                                    <span class="text-lg"><?= $c['flag'] ?></span>
                                    <div class="flex-1">
                                        <div class="text-xs"><?= $c['name'] ?></div>
                                        <div class="text-[9px] text-gray-400 font-mono" dir="ltr"><?= $code ?> (<?= $c['symbol'] ?>)</div>
                                    </div>
                                    <?php if($code == $currentCurrency): ?><i class="fas fa-check text-pri-500 text-[10px]"></i><?php endif; ?>
                                </button>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <nav class="max-w-7xl mx-auto px-4 py-4 flex items-center justify-between gap-6 relative z-40">
            <a href="index.php?page=home" class="flex items-center gap-3 text-white no-underline shrink-0 group">
                <div>
                    <div class="text-2xl font-black leading-none tracking-tight font-amiri"> 
                        <img width="80px" style="border-radius:15px;" src="../assets/images/logo.jpg" alt="تشافي">
                    </div>
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
                <a href="index.php?page=book_appointment" class="hidden md:flex items-center gap-2 py-2.5 px-5 rounded-xl bg-gradient-to-r from-gld-500 to-gld-600 text-pri-900 text-sm font-black hover:shadow-lg hover:shadow-gld-500/30 transition-all border border-gld-400">
                    <i class="fas fa-calendar-check text-base"></i><span>احجز جلسة</span>
                </a>
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
        
        <div class="border-t border-white/10 bg-black/10 backdrop-blur-md relative z-30">
            <div class="max-w-7xl mx-auto px-4 flex items-center justify-start md:justify-center overflow-x-auto py-3 text-sm no-sb">
                
                <a href="index.php?page=books" class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-white/70 hover:text-white hover:bg-white/10 transition whitespace-nowrap text-xs font-bold no-underline">
                    <i class="fas fa-book-open text-gld-400 text-[11px]"></i> المكتبة والكتب
                </a>

                <?php foreach($navPages as $navPage): ?>
                    <a href="index.php?page=cms&slug=<?= htmlspecialchars($navPage['slug']) ?>" class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-white/70 hover:text-white hover:bg-white/10 transition whitespace-nowrap text-xs font-bold no-underline">
                        <i class="fas fa-info-circle text-gld-400 text-[11px]"></i> <?= htmlspecialchars($navPage['title']) ?>
                    </a>
                <?php endforeach; ?>

                <?php foreach($navCategories as $cat): ?>
                    <a href="index.php?page=category&category_id=<?= $cat['id'] ?>" class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-white/70 hover:text-white hover:bg-white/10 transition whitespace-nowrap text-xs font-bold no-underline">
                        <i class="<?= htmlspecialchars($cat['icon_class'] ?? 'fas fa-folder') ?> text-gld-400 text-[11px]"></i> <?= htmlspecialchars($cat['name']) ?>
                    </a>
                <?php endforeach; ?>

            </div>
        </div>
    </header>

    <main class="flex-1 relative z-10 w-full" id="app">

    <script>
    function changeCurrency(code) {
        const fd = new FormData();
        fd.append('currency', code);
        fetch('ajax/set_currency.php', { method: 'POST', body: fd })
            .then(res => res.json())
            .then(data => {
                if(data.success) {
                    window.location.reload();
                } else {
                    alert('تعذر تغيير العملة');
                }
            });
    }

    document.addEventListener('DOMContentLoaded', () => {
        if (window.ChafiCurrency.code === 'SAR') return; 

        const rate = window.ChafiCurrency.rate;
        const symbol = window.ChafiCurrency.symbol;

        function convertPricesInDOM(node) {
            if (node.nodeType === 3) { 
                const text = node.nodeValue;
                const regex = /([\d,]+(?:\.\d+)?)\s*ر\.س/g;
                if (regex.test(text)) {
                    node.nodeValue = text.replace(regex, (match, numStr) => {
                        const cleanNum = parseFloat(numStr.replace(/,/g, ''));
                        if (isNaN(cleanNum)) return match;
                        const converted = (cleanNum * rate).toFixed(2);
                        const formatted = converted.replace(/\B(?=(\d{3})+(?!\d))/g, ",");
                        return formatted + ' ' + symbol;
                    });
                }
            } else if (node.nodeType === 1 && node.nodeName !== 'SCRIPT' && node.nodeName !== 'STYLE') {
                for (let i = 0; i < node.childNodes.length; i++) {
                    convertPricesInDOM(node.childNodes[i]);
                }
            }
        }

        const appBody = document.getElementById('app');
        if (appBody) {
            convertPricesInDOM(appBody);
        }
    });
    </script>