using System;
using System.ComponentModel.DataAnnotations;
using System.ComponentModel.DataAnnotations.Schema;

namespace Chafi.Models
{
    public class CartItem
    {
        [Key]
        [DatabaseGenerated(DatabaseGeneratedOption.Identity)]
        public int Id { get; set; }

        [Required]
        public string CartUserId { get; set; } = string.Empty;

        [Display(Name = "معرف المنتج")]
        public int? ProductId { get; set; }

        [Display(Name = "معرف الباقة")]
        public int? PackageId { get; set; }

        [Required]
        [Range(1, 100)]
        [Display(Name = "الكمية")]
        public int Quantity { get; set; } = 1;

        [Display(Name = "تاريخ الإضافة")]
        public DateTime AddedAt { get; set; } = DateTime.UtcNow;

        // --- العلاقات ---
        [ForeignKey(nameof(CartUserId))]
        public virtual Cart Cart { get; set; } = null!;

        [ForeignKey(nameof(ProductId))]
        public virtual Product? Product { get; set; }

        [ForeignKey(nameof(PackageId))]
        public virtual Package? Package { get; set; }

        [NotMapped]
        public decimal UnitPrice => Product?.Price ?? Package?.PackagePrice ?? 0;

        [NotMapped]
        public decimal TotalPrice => UnitPrice * Quantity;

        [NotMapped]
        public string ItemName => Product?.Name ?? Package?.Name ?? "عنصر غير معروف";

        [NotMapped]
        public string? ItemImage => Product?.ImageUrl ?? Package?.ImageUrl;
    }
}