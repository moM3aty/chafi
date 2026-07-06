// مسار الملف: Controllers/MediaController.cs

using System.Linq;
using System.Threading.Tasks;
using Microsoft.AspNetCore.Mvc;
using Microsoft.EntityFrameworkCore;
using Chafi.Models;

namespace Chafi.Controllers
{
    public class MediaController : Controller
    {
        private readonly ChafiDbContext _context;

        public MediaController(ChafiDbContext context)
        {
            _context = context;
        }

        // عرض تفاصيل المقطع الصوتي
        public async Task<IActionResult> AudioDetails(int id, string slug)
        {
            var audio = await _context.Audios
                .Include(a => a.Category)
                .FirstOrDefaultAsync(a => a.Id == id && a.IsActive);

            if (audio == null)
            {
                return NotFound();
            }

            // زيادة عدد الاستماعات أو الزيارات كإحصائية
            audio.ListenCount += 1;
            await _context.SaveChangesAsync();

            return View(audio);
        }

        // عرض تفاصيل الفيديو
        public async Task<IActionResult> VideoDetails(int id, string slug)
        {
            var video = await _context.Videos
                .Include(v => v.Category)
                .FirstOrDefaultAsync(v => v.Id == id && v.IsActive);

            if (video == null)
            {
                return NotFound();
            }

            // زيادة عدد المشاهدات
            video.ViewCount += 1;
            await _context.SaveChangesAsync();

            return View(video);
        }
    }
}