using System.ComponentModel.DataAnnotations;
using System.ComponentModel.DataAnnotations.Schema;

namespace Chafi.Models
{
    public class PackageItem
    {
        [Key]
        [DatabaseGenerated(DatabaseGeneratedOption.Identity)]
        public int Id { get; set; }

        [Required]
        public int PackageId { get; set; }

        [Required]
        public int ProductId { get; set; }

        [Required]
        [Range(1, 100)]
        [Display(Name = "الكمية")]
        public int Quantity { get; set; } = 1;

        // --- العلاقات ---
        [ForeignKey(nameof(PackageId))]
        public virtual Package Package { get; set; } = null!;

        [ForeignKey(nameof(ProductId))]
        public virtual Product Product { get; set; } = null!;

        [NotMapped]
        public decimal LineTotal => Product.Price * Quantity;
    }
}