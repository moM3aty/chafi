using System.ComponentModel.DataAnnotations;
using System.ComponentModel.DataAnnotations.Schema;

namespace Chafi.Models
{
    public class ProductImage
    {
        [Key]
        [DatabaseGenerated(DatabaseGeneratedOption.Identity)]
        public int Id { get; set; }

        [Required]
        [StringLength(500)]
        public string ImageUrl { get; set; } = string.Empty;

        [Required]
        public int ProductId { get; set; }

        [Display(Name = "الصورة الرئيسية")]
        public bool IsMain { get; set; } = false;

        [Display(Name = "ترتيب العرض")]
        public int DisplayOrder { get; set; } = 0;

        [Display(Name = "نص بديل")]
        [StringLength(200)]
        public string? AltText { get; set; }

        // --- العلاقات ---
        [ForeignKey(nameof(ProductId))]
        public virtual Product Product { get; set; } = null!;
    }
}