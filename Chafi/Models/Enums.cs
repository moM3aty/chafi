namespace Chafi.Models
{
    public enum Gender
    {
        Male = 0,
        Female = 1
    }

    public enum ProductType
    {
        Physical = 0,   // منتج مادي (زيت، ماء، عسل رقية)
        Digital = 1      // منتج رقمي (كتاب PDF، دليل)
    }

    public enum DiscountType
    {
        Percentage = 0,  // نسبة مئوية
        FixedAmount = 1  // مبلغ ثابت
    }

    public enum AdPosition
    {
        HeroSlider = 0,     // سلايدر رئيسي
        TopBanner = 1,      // بانر علوي
        SideBar = 2,        // شريط جانبي
        Popup = 3,          // نافذة منبثقة
        FooterBanner = 4,   // بانر أسفل
        CategoryBanner = 5  // بانر داخل القسم
    }

    public enum ContentType
    {
        Audio = 0,
        Video = 1,
        Product = 2
    }

    public enum OrderStatus
    {
        Pending = 0,        // قيد الانتظار
        Confirmed = 1,      // مؤكد
        Processing = 2,     // قيد التجهيز
        Shipped = 3,        // تم الشحن
        Delivered = 4,      // تم التسليم
        Cancelled = 5,      // ملغي
        Refunded = 6        // مسترد
    }

    public enum PaymentMethod
    {
        CreditCard = 0,
        BankTransfer = 1,
        Wallet = 2,
        CashOnDelivery = 3
    }

    public enum PaymentStatus
    {
        Pending = 0,
        Success = 1,
        Failed = 2,
        Refunded = 3
    }

    public enum ContentAccessLevel
    {
        Free = 0,
        Registered = 1,
        Paid = 2
    }
}