using System;
using System.ComponentModel.DataAnnotations;
using System.ComponentModel.DataAnnotations.Schema;

namespace Chafi.Models
{
    public class Payment : BaseEntity
    {
        [Required]
        public int OrderId { get; set; }

        [Required]
        [Column(TypeName = "decimal(18,2)")]
        [Display(Name = "المبلغ")]
        public decimal Amount { get; set; }

        [Required]
        [Display(Name = "طريقة الدفع")]
        public PaymentMethod Method { get; set; }

        [Required]
        [Display(Name = "حالة الدفع")]
        public PaymentStatus Status { get; set; } = PaymentStatus.Pending;

        [StringLength(200)]
        [Display(Name = "رقم المعاملة")]
        public string? TransactionId { get; set; }

        [Display(Name = "تاريخ الدفع")]
        public DateTime? PaidAt { get; set; }

        [StringLength(500)]
        [Display(Name = "بيانات إضافية")]
        public string? Metadata { get; set; }

        // --- العلاقات ---
        [ForeignKey(nameof(OrderId))]
        public virtual Order Order { get; set; } = null!;
    }
}