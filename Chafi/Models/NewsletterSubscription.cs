using System;
using System.ComponentModel.DataAnnotations;
using System.ComponentModel.DataAnnotations.Schema;

namespace Chafi.Models
{
    public class NewsletterSubscription
    {
        [Key]
        [DatabaseGenerated(DatabaseGeneratedOption.Identity)]
        public int Id { get; set; }

        [Required]
        [EmailAddress]
        public string Email { get; set; } = string.Empty;

        public bool IsActive { get; set; } = true;
        public DateTime SubscribedAt { get; set; } = DateTime.UtcNow;
    }
}