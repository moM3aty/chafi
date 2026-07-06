using System;
using System.Collections.Generic;
using System.ComponentModel.DataAnnotations;
using Microsoft.AspNetCore.Identity;

namespace Chafi.Models
{
    /// <summary>
    /// مستخدم النظام - يرث من IdentityUser
    /// </summary>
    public class ApplicationUser : IdentityUser
    {
        [Required(ErrorMessage = "الاسم الكامل مطلوب")]
        [StringLength(100, ErrorMessage = "الاسم لا يتجاوز 100 حرف")]
        [Display(Name = "الاسم الكامل")]
        public string FullName { get; set; } = string.Empty;

        [StringLength(500)]
        [Display(Name = "رابط الصورة الشخصية")]
        public string? ProfileImageUrl { get; set; }

        [Display(Name = "تاريخ الميلاد")]
        public DateTime? DateOfBirth { get; set; }

        [Display(Name = "الجنس")]
        public Gender Gender { get; set; } = Gender.Male;

        [Display(Name = "العنوان")]
        [StringLength(300)]
        public string? Address { get; set; }

        [Display(Name = "المدينة")]
        [StringLength(100)]
        public string? City { get; set; }

        [Display(Name = "الرمز البريدي")]
        [StringLength(20)]
        public string? PostalCode { get; set; }

        [Display(Name = "البلد")]
        [StringLength(100)]
        public string? Country { get; set; }

        [Display(Name = "موافق على الشروط")]
        public bool IsTermsAccepted { get; set; } = false;

        [Display(Name = "نشط")]
        public bool IsActive { get; set; } = true;

        [Display(Name = "تاريخ آخر دخول")]
        public DateTime? LastLoginAt { get; set; }

        // --- العلاقات ---
        public virtual ICollection<Order> Orders { get; set; } = new List<Order>();
        public virtual Cart? Cart { get; set; }
        public virtual ICollection<Review> Reviews { get; set; } = new List<Review>();
        public virtual ICollection<WishlistItem> WishlistItems { get; set; } = new List<WishlistItem>();
        public virtual ICollection<UserAudioProgress> AudioProgresses { get; set; } = new List<UserAudioProgress>();
        public virtual ICollection<UserVideoProgress> VideoProgresses { get; set; } = new List<UserVideoProgress>();
    }
}