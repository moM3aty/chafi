<?php
// مسار الملف: ajax/auth.php
// النسخة المُصلحة — تم إزالة المسافات غير المرئية وتنسيق الكود

session_start();
require_once '../config.php';

header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');

// قراءة البيانات من JSON body
$rawInput = file_get_contents("php://input");
$data = json_decode($rawInput, true);

// إذا لم يأتِ JSON، حاول من POST العادي
if (!$data) {
    $data = $_POST;
}

$action = isset($_GET['action']) ? $_GET['action'] : '';

if ($action == 'login') {
    // قبول اسمين مختلفين للتوافق
    $email = $data['email'] ?? $data['Email'] ?? '';
    $password = $data['password'] ?? $data['Password'] ?? '';

    // تنظيف المدخلات
    $email = trim($email);
    $password = trim($password);

    if (empty($email) || empty($password)) {
        echo json_encode(['success' => false, 'message' => 'يرجى إدخال البريد الإلكتروني وكلمة المرور.']);
        exit;
    }

    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND is_active = 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if (!$user) {
            echo json_encode(['success' => false, 'message' => 'البريد الإلكتروني غير مسجل أو الحساب معطل.']);
            exit;
        }

        if (!password_verify($password, $user['password'])) {
            echo json_encode(['success' => false, 'message' => 'كلمة المرور غير صحيحة.']);
            exit;
        }

        // تحديث آخر تسجيل دخول
        $pdo->prepare("UPDATE users SET last_login_at = NOW(), last_login_ip = ? WHERE id = ?")
            ->execute([$_SERVER['REMOTE_ADDR'] ?? '', $user['id']]);

        // تعيين الجلسة
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['full_name'];
        $_SESSION['user_role'] = $user['role'];

        echo json_encode([
            'success' => true,
            'message' => 'مرحباً بك ' . $user['full_name'] . '! تم تسجيل الدخول بنجاح.',
            'user_name' => $user['full_name'],
            'role' => $user['role']
        ]);

    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'حدث خطأ في قاعدة البيانات.']);
    }
}
elseif ($action == 'register') {
    // قبول اسمين مختلفين للتوافق
    $fullName = $data['full_name'] ?? $data['FullName'] ?? '';
    $email    = $data['email'] ?? $data['Email'] ?? '';
    $phone    = $data['phone_number'] ?? $data['PhoneNumber'] ?? '';
    $password = $data['password'] ?? $data['Password'] ?? '';

    // تنظيف
    $fullName = trim($fullName);
    $email    = trim($email);
    $phone    = trim($phone);
    $password = trim($password);

    // تحقق أساسي
    if (empty($fullName) || empty($email) || empty($password)) {
        echo json_encode(['success' => false, 'message' => 'يرجى ملء جميع الحقول المطلوبة.']);
        exit;
    }

    if (strlen($fullName) < 3) {
        echo json_encode(['success' => false, 'message' => 'الاسم يجب أن يكون 3 أحرف على الأقل.']);
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'صيغة البريد الإلكتروني غير صحيحة.']);
        exit;
    }

    if (strlen($password) < 6) {
        echo json_encode(['success' => false, 'message' => 'كلمة المرور يجب أن تكون 6 أحرف على الأقل.']);
        exit;
    }

    try {
        // تحقق هل البريد موجود
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            echo json_encode(['success' => false, 'message' => 'هذا البريد الإلكتروني مسجل مسبقاً. جرب تسجيل الدخول أو استخدم بريد آخر.']);
            exit;
        }

        // تشفير كلمة المرور
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // إدخال المستخدم
        $stmt = $pdo->prepare("INSERT INTO users (full_name, email, phone, password, role, is_active) VALUES (?, ?, ?, ?, 'User', 1)");
        $stmt->execute([$fullName, $email, $phone ?: null, $hashedPassword]);

        $newUserId = $pdo->lastInsertId();

        // تعيين الجلسة تلقائياً
        $_SESSION['user_id'] = $newUserId;
        $_SESSION['user_name'] = $fullName;
        $_SESSION['user_role'] = 'User';

        echo json_encode([
            'success' => true,
            'message' => 'تم إنشاء حسابك بنجاح! مرحباً بك ' . $fullName . '.',
            'user_name' => $fullName
        ]);

    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'حدث خطأ أثناء إنشاء الحساب. يرجى المحاولة لاحقاً.']);
    }
} 
elseif ($action == 'update_profile') {
    $fullName = $_POST['full_name'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';

    try {
        $stmt = $pdo->prepare("UPDATE users SET full_name=?, email=?, phone=? WHERE id=?");
        $stmt->execute([$fullName, $email, $phone, $_SESSION['user_id']]);
        echo json_encode(['success' => true, 'message' => 'تم تحديث بياناتك بنجاح!']);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'حدث خطأ: ' . $e->getMessage()]);
    }
}
else {
    echo json_encode(['success' => false, 'message' => 'عملية غير معروفة.']);
}
?>