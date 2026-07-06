using System;
using System.Collections.Generic;
using System.ComponentModel.DataAnnotations;
using System.ComponentModel.DataAnnotations.Schema;

namespace Chafi.Models
{
    public class Order : BaseEntity
    {
        [Required]
        [Display(Name = "رقم الطلب")]
        [StringLength(50)]
        public string OrderNumber { get; set; } = string.Empty;

        [Required]
        public string UserId { get; set; } = string.Empty;

        [Required]
        [Column(TypeName = "decimal(18,2)")]
        [Display(Name = "المجموع الفرعي")]
        public decimal SubTotal { get; set; }

        [Column(TypeName = "decimal(18,2)")]
        [Display(Name = "مبلغ الخصم")]
        public decimal DiscountAmount { get; set; } = 0;

        [Column(TypeName = "decimal(18,2)")]
        [Display(Name = "تكلفة الشحن")]
        public decimal ShippingCost { get; set; } = 0;

        [Required]
        [Column(TypeName = "decimal(18,2)")]
        [Display(Name = "الإجمالي")]
        public decimal TotalAmount { get; set; }

        [Required]
        [Display(Name = "حالة الطلب")]
        public OrderStatus Status { get; set; } = OrderStatus.Pending;

        [Required]
        [StringLength(500)]
        [Display(Name = "عنوان الشحن")]
        public string ShippingAddress { get; set; } = string.Empty;

        [StringLength(100)]
        [Display(Name = "المدينة")]
        public string City { get; set; } = string.Empty;

        [StringLength(100)]
        [Display(Name = "البلد")]
        public string Country { get; set; } = string.Empty;

        [StringLength(20)]
        [Display(Name = "الرمز البريدي")]
        public string? PostalCode { get; set; }

        [Required]
        [StringLength(20)]
        [Display(Name = "رقم الهاتف")]
        public string Phone { get; set; } = string.Empty;

        [StringLength(500)]
        [Display(Name = "ملاحظات")]
        public string? Notes { get; set; }

        [Display(Name = "كوبون الخصم المستخدم")]
        [StringLength(20)]
        public string? AppliedCouponCode { get; set; }

        public int? OfferId { get; set; }

        [Display(Name = "تاريخ التأكيد")]
        public DateTime? ConfirmedAt { get; set; }

        [Display(Name = "تاريخ الشحن")]
        public DateTime? ShippedAt { get; set; }

        [Display(Name = "تاريخ التسليم")]
        public DateTime? DeliveredAt { get; set; }

        [StringLength(100)]
        [Display(Name = "رقم تتبع الشحن")]
        public string? TrackingNumber { get; set; }

        // --- العلاقات ---
        [ForeignKey(nameof(UserId))]
        public virtual ApplicationUser User { get; set; } = null!;

        [ForeignKey(nameof(OfferId))]
        public virtual Offer? Offer { get; set; }

        public virtual ICollection<OrderItem> Items { get; set; } = new List<OrderItem>();
        public virtual Payment? Payment { get; set; }
        public virtual ICollection<OrderStatusHistory> StatusHistory { get; set; } = new List<OrderStatusHistory>();
    }
}