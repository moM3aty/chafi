using System.ComponentModel.DataAnnotations;
using System.ComponentModel.DataAnnotations.Schema;

namespace Chafi.Models
{
    public class ProductOffer
    {
        [Required]
        public int ProductId { get; set; }

        [Required]
        public int OfferId { get; set; }

        // --- العلاقات ---
        [ForeignKey(nameof(ProductId))]
        public virtual Product Product { get; set; } = null!;

        [ForeignKey(nameof(OfferId))]
        public virtual Offer Offer { get; set; } = null!;
    }
}