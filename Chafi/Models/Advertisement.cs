using System;
using System.ComponentModel.DataAnnotations;
using System.ComponentModel.DataAnnotations.Schema;

namespace Chafi.Models
{
    public class Advertisement : BaseEntity
    {
        [Required]
        [StringLength(200)]
        [Display(Name = "عنوان الإعلان")]
        public string Title { get; set; } = string.Empty;

        [StringLength(300)]
        [Display(Name = "العنوان الفرعي")]
        public string? Subtitle { get; set; }

        [Required]
        [StringLength(500)]
        [Display(Name = "صورة الإعلان")]
        public string ImageUrl { get; set; } = string.Empty;

        [StringLength(500)]
        [Display(Name = "رابط الإعلان")]
        public string? LinkUrl { get; set; }

        [Display(Name = "فتح في نافذة جديدة")]
        public bool OpenInNewTab { get; set; } = true;

        [Required]
        [Display(Name = "موضع الإعلان")]
        public AdPosition Position { get; set; }

        [Required]
        [Display(Name = "تاريخ البداية")]
        public DateTime StartDate { get; set; }

        [Display(Name = "تاريخ النهاية")]
        public DateTime? EndDate { get; set; }

        [Display(Name = "ترتيب العرض")]
        public int DisplayOrder { get; set; } = 0;

        [Display(Name = "مفعل")]
        public bool IsActive { get; set; } = true;

        [Display(Name = "عدد مرات الظهور")]
        public int ImpressionsCount { get; set; } = 0;

        [Display(Name = "عدد النقرات")]
        public int ClicksCount { get; set; } = 0;

        [NotMapped]
        public bool IsCurrentlyActive => IsActive
            && StartDate <= DateTime.UtcNow
            && (EndDate == null || EndDate > DateTime.UtcNow);
    }
}