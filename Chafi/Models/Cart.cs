using System;
using System.Collections.Generic;
using System.ComponentModel.DataAnnotations;
using System.ComponentModel.DataAnnotations.Schema;
using System.Linq;

namespace Chafi.Models
{
    public class Cart
    {
        [Key]
        [Required]
        public string UserId { get; set; } = string.Empty;

        [Display(Name = "تاريخ الإنشاء")]
        public DateTime CreatedAt { get; set; } = DateTime.UtcNow;

        [Display(Name = "تاريخ آخر تحديث")]
        public DateTime? UpdatedAt { get; set; }

        // --- العلاقات ---
        [ForeignKey(nameof(UserId))]
        public virtual ApplicationUser User { get; set; } = null!;

        public virtual ICollection<CartItem> Items { get; set; } = new List<CartItem>();

        [NotMapped]
        public decimal SubTotal => Items.Sum(i => i.TotalPrice);
        [NotMapped]
        public int TotalItems => Items.Sum(i => i.Quantity);
    }
}