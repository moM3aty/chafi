using Microsoft.EntityFrameworkCore.Metadata.Internal;
using System.Collections.Generic;
using System.ComponentModel.DataAnnotations;
using System.ComponentModel.DataAnnotations.Schema;

namespace Chafi.Models
{
    /// <summary>
    /// الأقسام - يدعم أقسام رئيسية وفرعية (Self-Referencing)
    /// </summary>
    public class Category : BaseEntity
    {
        [Required(ErrorMessage = "اسم القسم مطلوب")]
        [StringLength(150, ErrorMessage = "اسم القسم لا يتجاوز 150 حرف")]
        [Display(Name = "اسم القسم")]
        public string Name { get; set; } = string.Empty;

        [StringLength(2000, ErrorMessage = "الوصف لا يتجاوز 2000 حرف")]
        [Display(Name = "وصف القسم")]
        public string? Description { get; set; }

        [Display(Name = "الوصف المختصر")]
        [StringLength(300)]
        public string? ShortDescription { get; set; }

        [Display(Name = "رابط صورة القسم")]
        [StringLength(500)]
        public string? ImageUrl { get; set; }

        [Display(Name = "أيقونة القسم")]
        [StringLength(100)]
        public string? IconClass { get; set; }

        [Display(Name = "لون القسم")]
        [StringLength(7)]
        public string? ColorHex { get; set; }

        [Display(Name = "القسم الأب")]
        public int? ParentId { get; set; }

        [Display(Name = "ترتيب العرض")]
        [Range(0, 9999)]
        public int DisplayOrder { get; set; } = 0;

        [Display(Name = "نوع المحتوى الافتراضي")]
        public ContentType? DefaultContentType { get; set; }

        [Display(Name = "مفعل")]
        public bool IsActive { get; set; } = true;

        [Display(Name = "يظهر في القائمة الرئيسية")]
        public bool ShowInMainMenu { get; set; } = true;

        [Display(Name = "عدد الزيارات")]
        public int ViewsCount { get; set; } = 0;

        [Display(Name = "الslug")]
        [StringLength(200)]
        public string? Slug { get; set; }

        // --- العلاقات ---
        [ForeignKey(nameof(ParentId))]
        public virtual Category? Parent { get; set; }

        public virtual ICollection<Category> Children { get; set; } = new List<Category>();
        public virtual ICollection<Product> Products { get; set; } = new List<Product>();
        public virtual ICollection<Audio> Audios { get; set; } = new List<Audio>();
        public virtual ICollection<Video> Videos { get; set; } = new List<Video>();

        [NotMapped]
        public string FullPath => Parent != null ? $"{Parent.Name} > {Name}" : Name;

        [NotMapped]
        public int TotalItemsCount => Products.Count + Audios.Count + Videos.Count;
    }
}