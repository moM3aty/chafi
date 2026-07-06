using System;
using System.ComponentModel;
using System.ComponentModel.DataAnnotations;
using System.ComponentModel.DataAnnotations.Schema;

namespace Chafi.Models
{
    /// <summary>
    /// الكيان الأساسي - يحتوي على الحقول المشتركة بين جميع الجداول
    /// </summary>
    public abstract class BaseEntity
    {
        [Key]
        [DatabaseGenerated(DatabaseGeneratedOption.Identity)]
        public int Id { get; set; }

        [Required]
        public DateTime CreatedAt { get; set; } = DateTime.UtcNow;

        public DateTime? UpdatedAt { get; set; }

        [DefaultValue(false)]
        public bool IsDeleted { get; set; } = false;

        [NotMapped]
        public string CreatedAtFormatted => CreatedAt.ToString("yyyy-MM-dd HH:mm");
    }
}