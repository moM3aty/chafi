using System.Linq;
using System.Threading.Tasks;
using Microsoft.AspNetCore.Mvc;
using Microsoft.EntityFrameworkCore;
using Chafi.Models;

namespace Chafi.Controllers
{
    public class CategoryController : Controller
    {
        private readonly ChafiDbContext _context;

        public CategoryController(ChafiDbContext context)
        {
            _context = context;
        }

        // الأكشن هذا يحقق طلبك: فتح القسم ورؤية التعريف ثم المنتجات/الصوتيات/الفيديوهات تحته
        public async Task<IActionResult> Details(int id, string slug)
        {
            var category = await _context.Categories
                .Include(c => c.Children) // الأقسام الفرعية
                .Include(c => c.Products.Where(p => p.IsActive))
                .Include(c => c.Audios.Where(a => a.IsActive))
                .Include(c => c.Videos.Where(v => v.IsActive))
                .FirstOrDefaultAsync(c => c.Id == id && c.IsActive);

            if (category == null)
            {
                return NotFound();
            }

            // تحديث عدد الزيارات
            category.ViewsCount += 1;
            await _context.SaveChangesAsync();

            return View(category);
        }
    }
}