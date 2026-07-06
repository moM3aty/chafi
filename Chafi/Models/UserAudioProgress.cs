using System;
using System.ComponentModel.DataAnnotations;
using System.ComponentModel.DataAnnotations.Schema;

namespace Chafi.Models
{
    public class UserAudioProgress
    {
        [Key]
        [DatabaseGenerated(DatabaseGeneratedOption.Identity)]
        public int Id { get; set; }

        [Required]
        public string UserId { get; set; } = string.Empty;

        [Required]
        public int AudioId { get; set; }

        [Display(Name = "آخر ثانية تم الوصول إليها")]
        public int LastPositionSeconds { get; set; } = 0;

        [Display(Name = "مكتمل")]
        public bool IsCompleted { get; set; } = false;

        public DateTime LastListenAt { get; set; } = DateTime.UtcNow;

        // --- العلاقات ---
        [ForeignKey(nameof(UserId))]
        public virtual ApplicationUser User { get; set; } = null!;

        [ForeignKey(nameof(AudioId))]
        public virtual Audio Audio { get; set; } = null!;
    }
}