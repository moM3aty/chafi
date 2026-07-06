using System.ComponentModel.DataAnnotations;
using System.ComponentModel.DataAnnotations.Schema;

namespace Chafi.Models
{
    public class SiteSetting
    {
        [Key]
        [DatabaseGenerated(DatabaseGeneratedOption.Identity)]
        public int Id { get; set; }

        [Required]
        [StringLength(100)]
        public string Key { get; set; } = string.Empty;

        [Required]
        [Column(TypeName = "nvarchar(max)")]
        public string Value { get; set; } = string.Empty;

        [StringLength(200)]
        public string? Description { get; set; }
    }
}