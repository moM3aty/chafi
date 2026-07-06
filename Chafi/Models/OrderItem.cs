using System.ComponentModel.DataAnnotations;
using System.ComponentModel.DataAnnotations.Schema;

namespace Chafi.Models
{
    public class OrderItem
    {
        [Key]
        [DatabaseGenerated(DatabaseGeneratedOption.Identity)]
        public int Id { get; set; }

        [Required]
        public int OrderId { get; set; }

        public int? ProductId { get; set; }
        public int? PackageId { get; set; }

        [Required]
        [StringLength(200)]
        [Display(Name = "اسم العنصر")]
        public string ItemName { get; set; } = string.Empty;

        [Required]
        [Column(TypeName = "decimal(18,2)")]
        [Display(Name = "السعر الوحد")]
        public decimal UnitPrice { get; set; }

        [Required]
        [Range(1, 100)]
        [Display(Name = "الكمية")]
        public int Quantity { get; set; } = 1;

        [Required]
        [Column(TypeName = "decimal(18,2)")]
        [Display(Name = "الإجمالي")]
        public decimal TotalPrice { get; set; }

        // --- العلاقات ---
        [ForeignKey(nameof(OrderId))]
        public virtual Order Order { get; set; } = null!;

        [ForeignKey(nameof(ProductId))]
        public virtual Product? Product { get; set; }

        [ForeignKey(nameof(PackageId))]
        public virtual Package? Package { get; set; }
    }
}