<?php
// مسار الملف: pages/admin_offer_form.php
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['Admin', 'SuperAdmin'])) {
    echo "<script>window.location.href='index.php';</script>"; exit;
}
?>

<div class="max-w-4xl mx-auto px-4 py-8 mb-14 afiu">
    <div class="flex items-center justify-between mb-8">
        <h1 class="text-2xl font-black text-pri-900 font-amiri"><i class="fas fa-tags text-gld-500 ml-2"></i>إضافة عرض أو كود خصم</h1>
        <a href="index.php?page=admin_offers" class="cf-btn cf-btn-out cf-btn-sm bg-white text-xs"><i class="fas fa-arrow-right"></i> العودة للعروض</a>
    </div>

    <div class="erp-card p-6 sm:p-10 shadow-sm">
        <form method="post" action="#" onsubmit="event.preventDefault(); showToast('تم ربط الواجهة، يرجى تفعيل الجدول الخاص بالكوبونات في setup_db', 'ok'); setTimeout(() => window.location.href='index.php?page=admin_offers', 2000);">
            
            <div class="cf-group mb-6">
                <label class="cf-label">عنوان العرض <span class="req">*</span></label>
                <input type="text" name="title" class="cf-input" placeholder="مثال: خصم الشتاء 20%" required />
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                <div class="cf-group !mb-0">
                    <label class="cf-label">نوع الخصم <span class="req">*</span></label>
                    <select name="discount_type" class="cf-select" required>
                        <option value="percentage">نسبة مئوية (%)</option>
                        <option value="fixed">مبلغ ثابت (ر.س)</option>
                    </select>
                </div>

                <div class="cf-group !mb-0">
                    <label class="cf-label">قيمة الخصم <span class="req">*</span></label>
                    <input type="number" step="0.01" name="discount_value" class="cf-input" required />
                </div>

                <div class="cf-group !mb-0">
                    <label class="cf-label">كوبون الخصم (اختياري)</label>
                    <input type="text" name="coupon_code" class="cf-input" dir="ltr" placeholder="مثال: WINTER20" />
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div class="cf-group !mb-0">
                    <label class="cf-label">تاريخ بداية العرض <span class="req">*</span></label>
                    <input type="datetime-local" name="starts_at" class="cf-input" required />
                </div>

                <div class="cf-group !mb-0">
                    <label class="cf-label">تاريخ نهاية العرض <span class="req">*</span></label>
                    <input type="datetime-local" name="expires_at" class="cf-input" required />
                </div>
            </div>

            <div class="flex gap-6 mb-8 border-t border-gray-100 pt-6">
                <label class="cf-check-wrap font-bold text-pri-900">
                    <input type="checkbox" name="is_active" class="cf-check" checked /> تفعيل العرض الآن
                </label>
            </div>

            <button type="submit" class="cf-btn cf-btn-gld cf-btn-lg w-full md:w-auto"><i class="fas fa-check"></i> حفظ وإنشاء العرض</button>
        </form>
    </div>
</div>