using System;
using System.Collections.Generic;
using System.ComponentModel.DataAnnotations;
using System.ComponentModel.DataAnnotations.Schema;

namespace Chafi.Models
{
    public class Offer : BaseEntity
    {
        [Required]
        [StringLength(200)]
        [Display(Name = "عنوان العرض")]
        public string Title { get; set; } = string.Empty;

        [StringLength(1000)]
        [Display(Name = "وصف العرض")]
        public string? Description { get; set; }

        [StringLength(500)]
        [Display(Name = "صورة العرض")]
        public string? ImageUrl { get; set; }

        [Required]
        [Display(Name = "تاريخ البداية")]
        [DataType(DataType.DateTime)]
        public DateTime StartDate { get; set; }

        [Required]
        [Display(Name = "تاريخ النهاية")]
        [DataType(DataType.DateTime)]
        public DateTime EndDate { get; set; }

        [Required]
        [Display(Name = "نوع الخصم")]
        public DiscountType DiscountType { get; set; }

        [Required]
        [Range(0, 100, ErrorMessage = "قيمة الخصم غير صالحة")]
        [Column(TypeName = "decimal(18,2)")]
        [Display(Name = "قيمة الخصم")]
        public decimal DiscountValue { get; set; }

        [Display(Name = "كوبون الخصم")]
        [StringLength(20)]
        public string? CouponCode { get; set; }

        [Display(Name = "الحد الأقصى للاستخدام")]
        public int? MaxUsageCount { get; set; }

        [Display(Name = "عدد مرات الاستخدام")]
        public int UsageCount { get; set; } = 0;

        [Display(Name = "الحد الأدنى للطلب")]
        [Column(TypeName = "decimal(18,2)")]
        public decimal? MinOrderAmount { get; set; }

        [Display(Name = "مفعل")]
        public bool IsActive { get; set; } = true;

        // --- العلاقات ---
        public virtual ICollection<ProductOffer> ProductOffers { get; set; } = new List<ProductOffer>();
        public virtual ICollection<Order> Orders { get; set; } = new List<Order>();

        [NotMapped]
        public bool IsExpired => EndDate < DateTime.UtcNow;
        [NotMapped]
        public bool IsUpcoming => StartDate > DateTime.UtcNow;
        [NotMapped]
        public bool IsActiveNow => IsActive && !IsExpired && !IsUpcoming;
    }
}