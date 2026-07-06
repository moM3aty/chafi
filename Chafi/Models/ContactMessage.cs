using System;
using System.ComponentModel.DataAnnotations;
using System.ComponentModel.DataAnnotations.Schema;

namespace Chafi.Models
{
    public class ContactMessage : BaseEntity
    {
        [Required]
        [StringLength(100)]
        [Display(Name = "الاسم")]
        public string Name { get; set; } = string.Empty;

        [Required]
        [EmailAddress]
        [Display(Name = "البريد الإلكتروني")]
        public string Email { get; set; } = string.Empty;

        [StringLength(20)]
        [Display(Name = "رقم الهاتف")]
        public string? Phone { get; set; }

        [Required]
        [StringLength(200)]
        [Display(Name = "الموضوع")]
        public string Subject { get; set; } = string.Empty;

        [Required]
        [Column(TypeName = "nvarchar(max)")]
        [Display(Name = "الرسالة")]
        public string Message { get; set; } = string.Empty;

        [Display(Name = "مقروء")]
        public bool IsRead { get; set; } = false;

        public DateTime? ReadAt { get; set; }

        [StringLength(100)]
        [Display(Name = "IP العميل")]
        public string? ClientIp { get; set; }
    }
}