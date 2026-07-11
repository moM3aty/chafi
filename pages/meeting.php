<?php
// مسار الملف: pages/meeting.php
// الوظيفة: إنشاء اتصال فيديو مباشر آمن بين العميل والشيخ باستخدام Jitsi Meet

if (!isset($_SESSION['user_id'])) {
    echo "<script>window.location.href='index.php?page=home';</script>"; exit;
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    echo "<div style='text-align:center; padding:50px; color:red; font-weight:bold;'>رقم الجلسة غير صحيح</div>"; exit;
}

// جلب الموعد
$isAdmin = in_array($_SESSION['user_role'], ['Admin', 'SuperAdmin']);
$query = "SELECT * FROM appointments WHERE id = ?";
$params = [$id];

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$apt = $stmt->fetch();

if (!$apt) {
    echo "<div style='max-width:600px; margin:50px auto; padding:30px; background:#fef2f2; border-radius:15px; text-align:center; border:2px solid #fecaca; font-family:sans-serif;' dir='rtl'>
            <h2 style='color:#b91c1c;'>لا يمكنك الوصول لهذه الجلسة</h2>
            <p style='color:#ef4444;'>قد تكون الجلسة غير موجودة.</p>
          </div>";
    exit;
}

// للتحقق من هوية العميل، نقارن البريد
if (!$isAdmin) {
    $uStmt = $pdo->prepare("SELECT email FROM users WHERE id = ?");
    $uStmt->execute([$_SESSION['user_id']]);
    $uMail = $uStmt->fetchColumn();
    
    // إذا لم يكن الإيميل متطابقاً، نمنعه (مع تجاهل المسافات وحالة الأحرف)
    if (strtolower(trim($apt['email'])) !== strtolower(trim($uMail))) {
         echo "<div style='max-width:600px; margin:50px auto; padding:30px; background:#fef2f2; border-radius:15px; text-align:center; border:2px solid #fecaca; font-family:sans-serif;' dir='rtl'>
            <h2 style='color:#b91c1c;'>عذراً، هذه الجلسة مخصصة لحساب آخر</h2>
            <p style='color:#ef4444;'>البريد الإلكتروني المسجل في الحجز لا يطابق بريدك الحالي.</p>
            <a href='index.php?page=dashboard' style='display:inline-block; margin-top:20px; padding:10px 20px; background:#1a582a; color:#fff; text-decoration:none; border-radius:8px;'>العودة لحسابي</a>
          </div>";
        exit;
    }
}

// نمنع العميل من الدخول إذا لم تؤكد الإدارة الموعد
if ($apt['status'] !== 'Confirmed' && !$isAdmin) {
    echo "<div style='max-width:600px; margin:50px auto; padding:30px; background:#fefce8; border-radius:15px; text-align:center; border:2px solid #fef08a; font-family:sans-serif;' dir='rtl'>
            <h2 style='color:#a16207;'>الجلسة ليست جاهزة بعد</h2>
            <p style='color:#ca8a04;'>يرجى انتظار تحديد موعد وتأكيد الإدارة للطلب لتتمكن من الدخول.</p>
            <a href='index.php?page=dashboard' style='display:inline-block; margin-top:20px; padding:10px 20px; background:#1a582a; color:#fff; text-decoration:none; border-radius:8px;'>العودة لحسابي</a>
          </div>";
    exit;
}

// توليد اسم غرفة فريد وآمن
// تأكد أن الاسم لا يحتوي على مسافات أو رموز غير صالحة لـ Jitsi
$roomName = "ChafiSession_" . md5($apt['id'] . "ChafiSecKey2026");
$displayName = $isAdmin ? 'الشيخ المعالج' : htmlspecialchars($apt['full_name']);
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>جلسة رقية مباشرة - تشافي</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;700;900&family=Amiri:wght@700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body { font-family: 'Cairo', sans-serif; margin: 0; padding: 0; overflow: hidden; background-color: #111827; }
        .jitsi-container { height: calc(100vh - 64px); width: 100%; }
        /* إخفاء شريط التمرير */
        ::-webkit-scrollbar { width: 0; }
    </style>
</head>
<body class="flex flex-col h-screen w-screen">

    <!-- شريط الهيدر المخصص لغرفة الجلسة -->
    <div class="h-16 bg-gray-900 border-b border-gray-800 flex items-center justify-between px-4 sm:px-6 shrink-0 z-50">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-full bg-green-500/20 text-green-400 flex items-center justify-center text-lg animate-pulse">
                <i class="fas fa-video"></i>
            </div>
            <div>
                <h1 class="text-white font-bold text-sm sm:text-lg font-amiri tracking-wider">جلسة رقية مباشرة</h1>
                <p class="text-gray-400 text-xs flex items-center gap-1">
                    <span class="w-2 h-2 rounded-full bg-green-500 inline-block"></span> 
                    <?= $isAdmin ? 'أنت المدير (الشيخ)' : 'اتصال مشفر وآمن' ?>
                </p>
            </div>
        </div>
        <a href="index.php?page=<?= $isAdmin ? 'admin_appointments' : 'dashboard' ?>" class="flex items-center gap-2 bg-red-500/10 hover:bg-red-500 text-red-500 hover:text-white px-4 py-2 rounded-full transition-all text-xs sm:text-sm font-bold">
            <i class="fas fa-phone-slash"></i> إنهاء
        </a>
    </div>

    <!-- حاوية الميتينج -->
    <div id="meet" class="jitsi-container relative bg-gray-800 flex items-center justify-center">
        <!-- شاشة تحميل مبدئية خفيفة، تختفي بسرعة حتى لا تحجب إشعارات المتصفح -->
        <div id="loadingMeet" class="text-center p-6">
            <div class="w-16 h-16 rounded-full border-4 border-gray-600 border-t-green-500 animate-spin mx-auto mb-4"></div>
            <h3 class="text-white font-bold text-lg mb-2">جاري تهيئة الغرفة...</h3>
            <p class="text-gray-400 text-xs mt-2">ملاحظة هامة: يرجى الضغط على زر (السماح / Allow) عند طلب المتصفح استخدام الكاميرا والميكروفون.</p>
        </div>
    </div>

    <!-- استدعاء سكريبت Jitsi -->
    <script src="https://meet.jit.si/external_api.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', () => {
        const domain = 'meet.jit.si';
        const options = {
            roomName: '<?= $roomName ?>',
            width: '100%',
            height: '100%',
            parentNode: document.querySelector('#meet'),
            userInfo: {
                displayName: '<?= $displayName ?>'
            },
            configOverwrite: { 
                startWithAudioMuted: false,
                startWithVideoMuted: false,
                disableModeratorIndicator: true,
                prejoinPageEnabled: true, // تفعيل شاشة الانضمام ليتمكن المستخدم من الموافقة على الكاميرا بسهولة
                hideConferenceTimer: false
            },
            interfaceConfigOverwrite: {
                TOOLBAR_BUTTONS: [
                    'microphone', 'camera', 'desktop', 'fullscreen',
                    'fodeviceselection', 'hangup', 'profile', 'chat', 'settings', 'raisehand',
                    'videoquality', 'filmstrip', 'tileview'
                ],
                SHOW_JITSI_WATERMARK: false,
                SHOW_BRAND_WATERMARK: false,
                SHOW_POWERED_BY: false,
                DEFAULT_BACKGROUND: '#111827', 
            }
        };

        const loader = document.getElementById('loadingMeet');
        
        try {
            const api = new JitsiMeetExternalAPI(domain, options);
            
            // إزالة التحميل فورا بعد التهيئة لكي لا يغطي على طلبات المتصفح
            if(loader) loader.remove();

            // عند إنهاء المكالمة يتم توجيهه للخلف
            api.addEventListener('videoConferenceLeft', () => {
                window.location.href = 'index.php?page=<?= $isAdmin ? 'admin_appointments' : 'dashboard' ?>';
            });
        } catch (error) {
            if(loader) {
                loader.innerHTML = '<h3 class="text-red-500 font-bold">حدث خطأ أثناء الاتصال بالخادم. يرجى تحديث الصفحة أو إيقاف مانع الإعلانات (AdBlock).</h3>';
            }
        }
    });
    </script>
</body>
</html>