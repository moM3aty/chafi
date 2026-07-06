// مسار الملف: Models/Package.cs

using System;
using System.Collections.Generic;
using System.ComponentModel.DataAnnotations;
using System.ComponentModel.DataAnnotations.Schema;

namespace Chafi.Models
{
    public class Package : BaseEntity
    {
        [Required]
        [StringLength(200)]
        [Display(Name = "اسم الباقة")]
        public string Name { get; set; } = string.Empty;

        [Required]
        [StringLength(2000)]
        [Display(Name = "وصف الباقة")]
        public string Description { get; set; } = string.Empty;

        [Required]
        [Column(TypeName = "decimal(18,2)")]
        [Display(Name = "إجمالي السعر الأصلي")]
        public decimal OriginalTotalPrice { get; set; }

        [Required]
        [Column(TypeName = "decimal(18,2)")]
        [Display(Name = "سعر الباقة")]
        public decimal PackagePrice { get; set; }

        [StringLength(500)]
        [Display(Name = "صورة الباقة")]
        public string? ImageUrl { get; set; }

        [Display(Name = "مفعل")]
        public bool IsActive { get; set; } = true;

        [Display(Name = "الحد الأقصى للشراء")]
        public int? MaxPurchasesPerUser { get; set; }

        [Display(Name = "عدد المبيعات")]
        public int SalesCount { get; set; } = 0;

        [Display(Name = "عدد الزيارات ومشاهدة الباقة")]
        public int ViewsCount { get; set; } = 0;

        [Display(Name = "ترتيب العرض")]
        public int DisplayOrder { get; set; } = 0;

        [Display(Name = "باقة مميزة")]
        public bool IsFeatured { get; set; } = false;

        // --- العلاقات ---
        public virtual ICollection<PackageItem> Items { get; set; } = new List<PackageItem>();
        public virtual ICollection<OrderItem> OrderItems { get; set; } = new List<OrderItem>();

        [NotMapped]
        public decimal SavingsAmount => OriginalTotalPrice - PackagePrice;

        [NotMapped]
        public decimal SavingsPercentage => OriginalTotalPrice > 0
            ? Math.Round((SavingsAmount / OriginalTotalPrice) * 100)
            : 0;
    }
}