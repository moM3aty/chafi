// مسار الملف: Controllers/PackageController.cs

using System.Linq;
using System.Threading.Tasks;
using Microsoft.AspNetCore.Mvc;
using Microsoft.EntityFrameworkCore;
using Chafi.Models;

namespace Chafi.Controllers
{
    public class PackageController : Controller
    {
        private readonly ChafiDbContext _context;

        public PackageController(ChafiDbContext context)
        {
            _context = context;
        }

        // عرض تفاصيل الباقة ومكوناتها للعميل
        public async Task<IActionResult> Details(int id)
        {
            var package = await _context.Packages
                .Include(p => p.Items)
                    .ThenInclude(i => i.Product)
                .FirstOrDefaultAsync(p => p.Id == id && p.IsActive);

            if (package == null)
            {
                return NotFound();
            }

            // زيادة العداد
            package.ViewsCount += 1;
            await _context.SaveChangesAsync();

            return View(package);
        }

        // عرض جميع الباقات المتاحة
        public async Task<IActionResult> Index()
        {
            var packages = await _context.Packages
                .Include(p => p.Items)
                    .ThenInclude(i => i.Product)
                .Where(p => p.IsActive)
                .OrderBy(p => p.DisplayOrder)
                .ToListAsync();

            return View(packages);
        }
    }
}