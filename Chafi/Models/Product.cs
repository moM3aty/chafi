using System;
using System.Collections.Generic;
using System.ComponentModel.DataAnnotations;
using System.ComponentModel.DataAnnotations.Schema;

namespace Chafi.Models
{
    /// <summary>
    /// المنتجات - منتجات الرقية (زيوت، مياه، عسل، كتب، إلخ)
    /// </summary>
    public class Product : BaseEntity
    {
        [Required(ErrorMessage = "اسم المنتج مطلوب")]
        [StringLength(200)]
        [Display(Name = "اسم المنتج")]
        public string Name { get; set; } = string.Empty;

        [Required(ErrorMessage = "وصف المنتج مطلوب")]
        [Column(TypeName = "nvarchar(max)")]
        [Display(Name = "وصف المنتج")]
        public string Description { get; set; } = string.Empty;

        [Display(Name = "وصف مختصر")]
        [StringLength(500)]
        public string? ShortDescription { get; set; }

        [Required(ErrorMessage = "السعر مطلوب")]
        [Range(0, 999999.99, ErrorMessage = "السعر غير صالح")]
        [Column(TypeName = "decimal(18,2)")]
        [Display(Name = "السعر")]
        public decimal Price { get; set; }

        [Range(0, 999999.99)]
        [Column(TypeName = "decimal(18,2)")]
        [Display(Name = "السعر قبل الخصم")]
        public decimal? OldPrice { get; set; }

        [Required]
        [Range(0, 99999, ErrorMessage = "الكمية غير صالحة")]
        [Display(Name = "الكمية المتوفرة")]
        public int StockQuantity { get; set; }

        [Display(Name = "الحد الأدنى للطلب")]
        [Range(1, 100)]
        public int MinOrderQuantity { get; set; } = 1;

        [Display(Name = "الوزن (جرام)")]
        public double? WeightInGrams { get; set; }

        [Display(Name = "SKU")]
        [StringLength(50)]
        public string? SKU { get; set; }

        [Display(Name = "رابط الصورة الرئيسية")]
        [StringLength(500)]
        public string? ImageUrl { get; set; }

        [Required]
        [Display(Name = "القسم")]
        public int CategoryId { get; set; }

        [Required]
        [Display(Name = "نوع المنتج")]
        public ProductType ProductType { get; set; } = ProductType.Physical;

        [Display(Name = "منتج مميز")]
        public bool IsFeatured { get; set; } = false;

        [Display(Name = "منتج جديد")]
        public bool IsNew { get; set; } = false;

        [Display(Name = "مفعل")]
        public bool IsActive { get; set; } = true;

        [Display(Name = "عدد الزيارات")]
        public int ViewsCount { get; set; } = 0;

        [Display(Name = "عدد المبيعات")]
        public int SalesCount { get; set; } = 0;

        [Display(Name = "متوسط التقييم")]
        [Range(0, 5)]
        public double AverageRating { get; set; } = 0;

        [Display(Name = "عدد التقييمات")]
        public int ReviewsCount { get; set; } = 0;

        [Display(Name = "Slug")]
        [StringLength(200)]
        public string? Slug { get; set; }

        // --- العلاقات ---
        [ForeignKey(nameof(CategoryId))]
        public virtual Category Category { get; set; } = null!;

        public virtual ICollection<ProductImage> Images { get; set; } = new List<ProductImage>();
        public virtual ICollection<OrderItem> OrderItems { get; set; } = new List<OrderItem>();
        public virtual ICollection<Review> Reviews { get; set; } = new List<Review>();
        public virtual ICollection<PackageItem> PackageItems { get; set; } = new List<PackageItem>();
        public virtual ICollection<ProductOffer> ProductOffers { get; set; } = new List<ProductOffer>();
        public virtual ICollection<WishlistItem> WishlistItems { get; set; } = new List<WishlistItem>();

        [NotMapped]
        public decimal DiscountPercentage => OldPrice.HasValue && OldPrice > 0
            ? Math.Round((1 - Price / OldPrice.Value) * 100)
            : 0;

        [NotMapped]
        public bool InStock => StockQuantity > 0;

        [NotMapped]
        public bool HasDiscount => OldPrice.HasValue && OldPrice > Price;
    }
}