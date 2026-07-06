using System.Linq;
using System.Threading.Tasks;
using Microsoft.AspNetCore.Mvc;
using Microsoft.EntityFrameworkCore;
using Chafi.Models;

namespace Chafi.Controllers
{
    public class ProductController : Controller
    {
        private readonly ChafiDbContext _context;

        public ProductController(ChafiDbContext context)
        {
            _context = context;
        }

        // عرض تفاصيل المنتج للبيع
        public async Task<IActionResult> Details(int id, string slug)
        {
            var product = await _context.Products
                .Include(p => p.Category)
                .Include(p => p.Images)
                .Include(p => p.Reviews.Where(r => r.IsApproved))
                .FirstOrDefaultAsync(p => p.Id == id && p.IsActive);

            if (product == null)
            {
                return NotFound();
            }

            product.ViewsCount += 1;
            await _context.SaveChangesAsync();

            return View(product);
        }

        // صفحة عرض جميع المنتجات مع الفلترة
        public async Task<IActionResult> Index(int? categoryId)
        {
            var query = _context.Products
                .Include(p => p.Category)
                .Where(p => p.IsActive);

            if (categoryId.HasValue)
            {
                query = query.Where(p => p.CategoryId == categoryId.Value);
            }

            var products = await query.OrderByDescending(p => p.CreatedAt).ToListAsync();
            return View(products);
        }
    }
}