using System;
using System.ComponentModel.DataAnnotations;
using System.ComponentModel.DataAnnotations.Schema;

namespace Chafi.Models
{
    public class OrderStatusHistory
    {
        [Key]
        [DatabaseGenerated(DatabaseGeneratedOption.Identity)]
        public int Id { get; set; }

        [Required]
        public int OrderId { get; set; }

        [Required]
        public OrderStatus Status { get; set; }

        [StringLength(500)]
        public string? Notes { get; set; }

        public DateTime ChangedAt { get; set; } = DateTime.UtcNow;

        [StringLength(100)]
        public string? ChangedBy { get; set; }

        // --- العلاقات ---
        [ForeignKey(nameof(OrderId))]
        public virtual Order Order { get; set; } = null!;
    }
}