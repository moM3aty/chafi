using System;
using System.Collections.Generic;
using System.ComponentModel.DataAnnotations;
using System.ComponentModel.DataAnnotations.Schema;

namespace Chafi.Models
{
    public class Video : BaseEntity
    {
        [Required]
        [StringLength(200)]
        [Display(Name = "عنوان الفيديو")]
        public string Title { get; set; } = string.Empty;

        [StringLength(2000)]
        [Display(Name = "وصف الفيديو")]
        public string? Description { get; set; }

        [Required]
        [StringLength(500)]
        [Display(Name = "رابط الفيديو")]
        public string VideoUrl { get; set; } = string.Empty;

        [StringLength(500)]
        [Display(Name = "صورة المصغرة")]
        public string? ThumbnailUrl { get; set; }

        [Display(Name = "المدة (ثواني)")]
        public int DurationSeconds { get; set; }

        [Display(Name = "المقدم / الراقي")]
        [StringLength(150)]
        public string? Presenter { get; set; }

        [Required]
        [Display(Name = "القسم")]
        public int CategoryId { get; set; }

        [Display(Name = "مستوى الوصول")]
        public ContentAccessLevel AccessLevel { get; set; } = ContentAccessLevel.Free;

        [Display(Name = "السعر (إذا كان مدفوع)")]
        [Column(TypeName = "decimal(18,2)")]
        public decimal? Price { get; set; }

        [Display(Name = "عدد المشاهدات")]
        public int ViewCount { get; set; } = 0;

        [Display(Name = "عدد الإعجابات")]
        public int LikesCount { get; set; } = 0;

        [Display(Name = "مفعل")]
        public bool IsActive { get; set; } = true;

        [Display(Name = "ترتيب العرض")]
        public int DisplayOrder { get; set; } = 0;

        [Display(Name = "Slug")]
        [StringLength(200)]
        public string? Slug { get; set; }

        // --- العلاقات ---
        [ForeignKey(nameof(CategoryId))]
        public virtual Category Category { get; set; } = null!;

        public virtual ICollection<UserVideoProgress> UserProgresses { get; set; } = new List<UserVideoProgress>();

        [NotMapped]
        public string FormattedDuration => TimeSpan.FromSeconds(DurationSeconds).ToString(@"hh\:mm\:ss");
    }
}