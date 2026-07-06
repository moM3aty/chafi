using System.Linq;
using System.Threading.Tasks;
using Microsoft.AspNetCore.Mvc;
using Microsoft.EntityFrameworkCore;
using Chafi.Models;
using Chafi.ViewModels;

namespace Chafi.Controllers
{
    public class HomeController : Controller
    {
        private readonly ChafiDbContext _context;

        public HomeController(ChafiDbContext context)
        {
            _context = context;
        }

        public async Task<IActionResult> Index()
        {
            // جلب البيانات بشكل ديناميكي من قاعدة البيانات لعرضها في الرئيسية
            var viewModel = new HomeViewModel
            {
                // جلب السلايدر الإعلاني
                HeroSliders = await _context.Advertisements
                    .Where(a => a.IsActive && a.Position == AdPosition.HeroSlider)
                    .OrderBy(a => a.DisplayOrder)
                    .ToListAsync(),

                // جلب الأقسام الرئيسية التي تظهر في الرئيسية
                MainCategories = await _context.Categories
                    .Where(c => c.IsActive && c.ParentId == null)
                    .OrderBy(c => c.DisplayOrder)
                    .Take(6)
                    .ToListAsync(),

                // جلب المنتجات المميزة (التي اختارها الأدمن لتظهر في الرئيسية)
                FeaturedProducts = await _context.Products
                    .Include(p => p.Category)
                    .Where(p => p.IsActive && p.IsFeatured)
                    .OrderByDescending(p => p.CreatedAt)
                    .Take(8)
                    .ToListAsync(),

                // جلب أحدث الصوتيات (مدفوعة)
                LatestAudios = await _context.Audios
                    .Include(a => a.Category)
                    .Where(a => a.IsActive)
                    .OrderByDescending(a => a.CreatedAt)
                    .Take(4)
                    .ToListAsync(),

                // جلب أحدث الفيديوهات
                LatestVideos = await _context.Videos
                    .Include(v => v.Category)
                    .Where(v => v.IsActive)
                    .OrderByDescending(v => v.CreatedAt)
                    .Take(3)
                    .ToListAsync(),

                // جلب الباقات المميزة
                FeaturedPackages = await _context.Packages
                    .Include(p => p.Items)
                    .Where(p => p.IsActive && p.IsFeatured)
                    .OrderByDescending(p => p.CreatedAt)
                    .Take(3)
                    .ToListAsync()
            };

            return View(viewModel);
        }
    }
}