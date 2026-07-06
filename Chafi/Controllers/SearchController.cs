// مسار الملف: Controllers/SearchController.cs

using System.Linq;
using System.Threading.Tasks;
using Microsoft.AspNetCore.Mvc;
using Microsoft.EntityFrameworkCore;
using Chafi.Models;
using Chafi.ViewModels;

namespace Chafi.Controllers
{
    public class SearchController : Controller
    {
        private readonly ChafiDbContext _context;

        public SearchController(ChafiDbContext context)
        {
            _context = context;
        }

        // معالجة طلبات البحث من شريط البحث في الهيدر
        public async Task<IActionResult> Index(string q)
        {
            var viewModel = new SearchViewModel { Query = q ?? "" };

            if (!string.IsNullOrWhiteSpace(q))
            {
                q = q.ToLower();

                // البحث في المنتجات المادية والرقمية
                viewModel.Products = await _context.Products
                    .Include(p => p.Category)
                    .Where(p => p.IsActive && (p.Name.ToLower().Contains(q) || p.Description.ToLower().Contains(q)))
                    .ToListAsync();

                // البحث في الصوتيات
                viewModel.Audios = await _context.Audios
                    .Include(a => a.Category)
                    .Where(a => a.IsActive && (a.Title.ToLower().Contains(q) || a.Narrator.ToLower().Contains(q)))
                    .ToListAsync();

                // البحث في الفيديوهات
                viewModel.Videos = await _context.Videos
                    .Include(v => v.Category)
                    .Where(v => v.IsActive && (v.Title.ToLower().Contains(q) || v.Presenter.ToLower().Contains(q)))
                    .ToListAsync();
            }

            return View(viewModel);
        }
    }
}