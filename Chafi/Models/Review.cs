using System.ComponentModel.DataAnnotations;
using System.ComponentModel.DataAnnotations.Schema;

namespace Chafi.Models
{
    public class Review : BaseEntity
    {
        [Required]
        public string UserId { get; set; } = string.Empty;

        [Required]
        public int ProductId { get; set; }

        [Required]
        [Range(1, 5, ErrorMessage = "التقييم بين 1 و 5")]
        [Display(Name = "التقييم")]
        public int Rating { get; set; }

        [StringLength(1000)]
        [Display(Name = "التعليق")]
        public string? Comment { get; set; }

        [Display(Name = "معتمد")]
        public bool IsApproved { get; set; } = false;

        // --- العلاقات ---
        [ForeignKey(nameof(UserId))]
        public virtual ApplicationUser User { get; set; } = null!;

        [ForeignKey(nameof(ProductId))]
        public virtual Product Product { get; set; } = null!;
    }
}